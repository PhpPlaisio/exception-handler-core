<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler\Helper;

use SetBased\Helper\CodeStore\PhpCodeStore;

/**
 * Class for generating the PHP code of exception handlers.
 */
class ExceptionHandlerCodeGenerator
{
  //--------------------------------------------------------------------------------------------------------------------
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
   * @param string  $class     The fully qualified class name.
   * @param array[] $allAgents The metadata of the exception agents.
   *
   * @return string
   */
  public function generateCode(string $class, array $allAgents): string
  {
    $parts     = explode('\\', $class);
    $class     = array_pop($parts);
    $namespace = implode('\\', $parts);

    $this->generateHeader($namespace);
    $this->generateClass($class, $allAgents);
    $this->generateTrailer();

    return $this->store->getCode();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the PHP code of the class definition.
   *
   * @param string  $class     The class name.
   * @param array[] $allAgents The metadata of the exception agents.
   */
  private function generateClass(string $class, array $allAgents): void
  {
    $this->store->append('/**');
    $this->store->append(' * Concrete implementation of the exception handler.', false);
    $this->store->append(' */', false);
    $this->store->append(sprintf('class %s implements ExceptionHandler', $class));
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
    $this->store->append('use SetBased\Abc\ExceptionHandler\ExceptionHandler;');
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
    $this->store->append(sprintf('public function %s(\\Throwable $exception): void', $method));
    $this->store->append('{');
    $this->store->append('switch (true)');
    $this->store->append('{');
    $first = true;
    foreach ($agents as $agent)
    {
      if (!$first) $this->store->append('');

      $this->store->append(sprintf("case is_a(\$exception, '%s'):", $agent['type']));
      $this->store->append(sprintf('/** @var \\%s $exception */', $agent['type']));
      $this->store->append(sprintf("\$handler = new \\%s();", $agent['class']));
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
}

//----------------------------------------------------------------------------------------------------------------------
