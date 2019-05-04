<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler\Helper;

use SetBased\Abc\Console\Style\AbcStyle;

/**
 * Command for generation the code for the core's exception handler.
 */
class ExceptionHandlerMetadataExtractor
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The path of the abc.xml config file.
   *
   * @var string
   */
  private $configFilename;

  /**
   * The number of errors occurred.
   *
   * @var int
   */
  private $errorCount = 0;

  /**
   * The output decorator.
   *
   * @var AbcStyle
   */
  private $io;

  /**
   * The names of the exceptions handlers.
   *
   * @var array
   */
  private $names = ['handlePrepareException',
                    'handleConstructException',
                    'handleResponseException',
                    'handleFinalizeException'];

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * ExceptionHandlerMetadataExtractor constructor.
   *
   * @param AbcStyle $io             The output decorator.
   * @param string   $configFilename The path of the abc.xml config file.
   */
  public function __construct(AbcStyle $io, string $configFilename)
  {
    $this->io             = $io;
    $this->configFilename = $configFilename;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Compares two handlers.
   *
   * @param array $agent1 The first agent.
   * @param array $agent2 The second agent.
   *
   * @return int
   */
  public static function compareAgents(array $agent1, array $agent2): int
  {
    $reflection1 = new \ReflectionClass($agent1['type']);
    $reflection2 = new \ReflectionClass($agent2['type']);

    if ($reflection1->isSubclassOf($agent2['type'])) return -1;
    if ($reflection2->isSubclassOf($agent1['type'])) return 1;

    if ($agent1['class']==$agent2['class']) return 0;

    return ($agent1['class']<$agent2['class']) ? -1 : 1;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts exception handlers from abc.xml and reflection of classes.
   */
  public function extractExceptionAgents(): array
  {
    $ret = [];
    foreach ($this->names as $name)
    {
      $ret[$name] = [];
    }

    $agentNames = $this->readExceptionAgents();

    foreach ($agentNames as $agentName)
    {
      $this->io->writeln(sprintf('Processing class %s', $agentName));

      try
      {
        $reflectionClass = new \ReflectionClass($agentName);
        $methods         = $reflectionClass->getMethods();
        $count           = 0;

        foreach ($methods as $method)
        {
          $basename = $this->extractExceptionHandlerBaseName($method->name);
          if ($basename!==null)
          {
            $exceptionClass = $this->extractExceptionClass($reflectionClass, $method->name);
            if ($exceptionClass!==null)
            {
              $ret[$basename][] = ['class'  => $reflectionClass->getName(),
                                   'method' => $method->name,
                                   'type'   => $exceptionClass];
              $count++;
            }
            else
            {
              $this->io->error(sprintf('Method %s must have exactly one argument and the type of this argument must be'.
                                       ' a child class of Throwable', $method->name));

              $this->errorCount++;
            }
          }
        }

        if ($count===0)
        {
          $this->io->error(sprintf('Class %s does not have any methods for exception handling', $reflectionClass->getName()));

          $this->errorCount++;
        }
      }
      catch (\ReflectionException $e)
      {
        $this->io->error($e->getMessage());

        $this->errorCount++;
      }
    }

    foreach ($ret as &$handler)
    {
      usort($handler, [self::class, 'compareAgents']);
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the class of the exception if a method has exactly one argument and the type of the argument is a
   * Throwable.
   *
   * @param \ReflectionClass $reflectionClass The reflection class.
   * @param string           $method          The name of the method.
   *
   * @return string|null
   *
   * @throws \ReflectionException
   */
  private function extractExceptionClass(\ReflectionClass $reflectionClass, string $method): ?string
  {
    $reflectionMethod = $reflectionClass->getMethod($method);

    if ($reflectionMethod->getNumberOfParameters()!==1)
    {
      return null;
    }

    $arguments = $reflectionMethod->getParameters();
    $argument  = $arguments[0];

    $class = $argument->getClass();
    if ($class===null)
    {
      return null;
    }

    if (!$class->isSubclassOf('Throwable') && $class->getName()!=='Throwable')
    {
      return null;
    }

    return $class->getName();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the basename of a method if the and only if the method is an exception handler.
   *
   * @param string $method The name of the method.
   *
   * @return string|null
   */
  private function extractExceptionHandlerBaseName(string $method): ?string
  {
    foreach ($this->names as $name)
    {
      if (substr($method, 0, strlen($name))==$name)
      {
        return $name;
      }
    }

    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the exception agents found in the abc.xml file.
   *
   * @return string[]
   */
  private function readExceptionAgents(): array
  {
    if (!is_file($this->configFilename))
    {
      throw new \RuntimeException(sprintf('File %s not found', $this->configFilename));
    }

    $helper = new AbcXmlHelper($this->configFilename);
    $agents = $helper->extractExceptionAgents();

    return $agents;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
