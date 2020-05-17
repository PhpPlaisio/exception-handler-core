<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Helper;

use Plaisio\ExceptionHandler\ExceptionHandler;
use Plaisio\PlaisioObject;
use SetBased\Helper\CodeStore\Importing;
use SetBased\Helper\CodeStore\PhpCodeStore;

/**
 * Class for generating the PHP code of exception handlers.
 */
class ExceptionHandlerCodeGenerator
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The helper object for importing classes.
   *
   * @var Importing
   */
  private $importing;

  /**
   * The PHP code store.
   *
   * @var PhpCodeStore
   */
  private $store;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   */
  public function __construct()
  {
    $this->store = new PhpCodeStore();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the PHP code of the exception handler.
   *
   * @param string  $fullyQualifiedName The fully qualified class name.
   * @param array[] $allAgents          The metadata of the exception agents.
   *
   * @return string
   */
  public function generateCode(string $fullyQualifiedName, array $allAgents): string
  {
    $parts     = explode('\\', $fullyQualifiedName);
    $class     = array_pop($parts);
    $namespace = ltrim(implode('\\', $parts), '\\');

    $this->importing = new Importing($namespace);
    $this->importing->addClass(ExceptionHandler::class);
    $this->importing->addClass(PlaisioObject::class);
    $this->importClasses($allAgents);

    $this->importing->prepare();

    $this->generateHeader($namespace);
    $this->generateClass($class, PlaisioObject::class, $allAgents);
    $this->generateTrailer();

    return $this->store->getCode();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the PHP code of the class definition.
   *
   * @param string  $handlerClass The class name of the exception handler.
   * @param string  $kernelClass  The class name of the kernel of PhpPlaisio.
   * @param array[] $allAgents    The metadata of the exception agents.
   */
  private function generateClass(string $handlerClass, string $kernelClass, array $allAgents): void
  {
    $this->store->append('/**');
    $this->store->append(' * Concrete implementation of the exception handler.', false);
    $this->store->append(' */', false);
    $this->store->append(sprintf('class %s extends %s implements ExceptionHandler',
                                 $handlerClass,
                                 $this->importing->simplyFullyQualifiedName($kernelClass)));
    $this->store->append('{');
    foreach ($allAgents as $name => $agents)
    {
      $this->generateMethod($name, $agents);
    }
    $this->store->appendSeparator();
    $this->store->append('}');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates all PHP code just before the class definition.
   *
   * @param string $namespace The namespace.
   */
  private function generateHeader(string $namespace): void
  {
    $this->store->append('<?php');
    $this->store->append('declare(strict_types=1);');
    $this->store->append('');
    $this->store->append(sprintf('namespace %s;', $namespace));
    $this->store->append('');
    $this->store->append($this->importing->imports());
    $this->store->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates a method for exception handling.
   *
   * @param string  $method The name of the method.
   * @param array[] $agents The metadata of the exception agents.
   */
  private function generateMethod(string $method, array $agents)
  {
    $this->store->appendSeparator();
    $this->store->append('/**');
    $this->store->append(' * @inheritdoc', false);
    $this->store->append(' */', false);
    $this->store->append(sprintf('public function %s(%s $exception): void',
                                 $method,
                                 $this->importing->simplyFullyQualifiedName('\\Throwable')));
    $this->store->append('{');
    $this->store->append('switch (true)');
    $this->store->append('{');
    $first = true;
    foreach ($agents as $agent)
    {
      if (!$first) $this->store->append('');

      $this->store->append(sprintf("case is_a(\$exception, %s::class):",
                                   $this->importing->simplyFullyQualifiedName($agent['type'])));
      $this->store->append(sprintf('/** @var %s $exception */',
                                   $this->importing->simplyFullyQualifiedName($agent['type'])));
      $this->store->append(sprintf("\$handler = new %s(\$this);",
                                   $this->importing->simplyFullyQualifiedName($agent['class'])));
      $this->store->append(sprintf('$handler->%s($exception);', $agent['method']));
      $this->store->append('break;');

      $first = false;
    }
    $this->store->append('}');
    $this->store->append('}');
    $this->store->append('');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates all PHP code just after the class definition.
   */
  private function generateTrailer(): void
  {
    $this->store->append('');
    $this->store->appendSeparator();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds classes of  exception agents to the importing helper object.
   *
   * @param array[] $allAgents The metadata of the exception agents.
   */
  private function importClasses(array $allAgents): void
  {
    foreach ($allAgents as $name => $agents)
    {
      foreach ($agents as $agent)
      {
        $this->importing->addClass($agent['type']);
        $this->importing->addClass($agent['class']);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
