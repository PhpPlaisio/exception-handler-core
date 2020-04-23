<?php
declare(strict_types=1);

namespace SetBased\Stratum\Test\Application;

use PHPUnit\Framework\TestCase;
use Plaisio\Console\Application\PlaisioApplication;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Test cases for the plaisio:generate-core-exception-handler command.
 */
class GenerateExceptionHandlerCoreTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  public function testExecute(): void
  {
    $_ENV['PLAISIO_CONFIG'] = __DIR__.'/plaisio.xml';

    $application = new PlaisioApplication();
    $application->setAutoExit(false);

    $tester = new ApplicationTester($application);
    $tester->run(['command' => 'plaisio:generate-core-exception-handler']);

    self::assertSame(0, $tester->getStatusCode(), $tester->getDisplay());

    self::assertFileEquals('test/Command/Foo.txt', 'test/Command/Foo.php');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
