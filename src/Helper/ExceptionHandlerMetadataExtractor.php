<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Helper;

use Plaisio\Console\Helper\PlaisioXmlPathHelper;
use Plaisio\Console\Style\PlaisioStyle;

/**
 * Command for generation the code for the core's exception handler.
 */
class ExceptionHandlerMetadataExtractor
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The number of errors occurred.
   *
   * @var int
   */
  private int $errorCount = 0;

  /**
   * The output decorator.
   *
   * @var PlaisioStyle
   */
  private PlaisioStyle $io;

  /**
   * The names of the exceptions handlers.
   *
   * @var array
   */
  private array $names = ['handlePrepareException',
                          'handleConstructException',
                          'handleResponseException',
                          'handleFinalizeException'];

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * ExceptionHandlerMetadataExtractor constructor.
   *
   * @param PlaisioStyle $io The output decorator.
   */
  public function __construct(PlaisioStyle $io)
  {
    $this->io = $io;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Compares two handlers.
   *
   * @param array $agent1 The first agent.
   * @param array $agent2 The second agent.
   *
   * @return int
   *
   * @throws \ReflectionException
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
   * Extracts exception handlers from plaisio.xml and reflection of classes.
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
      $this->io->text(sprintf('Processing class %s', $agentName));

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

    $type = $argument->getType();
    if ($type===null || !is_a($type, \ReflectionNamedType::class))
    {
      return null;
    }

    $class = $type->getName();
    if (is_callable($class))
    {
      return null;
    }

    $reflectionType = new \ReflectionClass($class);
    if (!$reflectionType->isSubclassOf('Throwable') && $reflectionType->getName()!=='Throwable')
    {
      return null;
    }

    return $reflectionType->getName();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the basename of a method if and only if the method is an exception handler.
   *
   * @param string $method The name of the method.
   *
   * @return string|null
   */
  private function extractExceptionHandlerBaseName(string $method): ?string
  {
    foreach ($this->names as $name)
    {
      if (str_starts_with($method, $name))
      {
        return $name;
      }
    }

    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the exception agents found in the plaisio.xml file.
   *
   * @return string[]
   */
  private function readExceptionAgents(): array
  {
    $helper = new PlaisioXmlQueryHelper(PlaisioXmlPathHelper::plaisioXmlPath('exception'));

    return $helper->queryExceptionAgents();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
