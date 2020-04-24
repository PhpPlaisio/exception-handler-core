<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Command;

use Plaisio\Console\Command\PlaisioCommand;
use Plaisio\Console\Helper\TwoPhaseWrite;
use Plaisio\ExceptionHandler\Helper\ExceptionHandlerCodeGenerator;
use Plaisio\ExceptionHandler\Helper\ExceptionHandlerMetadataExtractor;
use Plaisio\ExceptionHandler\Helper\PlaisioXmlHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generation the code for the core's exception handler.
 */
class GenerateExceptionHandlerCommand extends PlaisioCommand
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function configure()
  {
    $this->setName('plaisio:generate-core-exception-handler')
         ->setDescription('Generates the code for the core\'s exception handler');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->io->title('Plaisio: Generate Core Exception Handler');

    $metadataExtractor = new ExceptionHandlerMetadataExtractor($this->io);
    $handlers          = $metadataExtractor->extractExceptionAgents();

    $xmlHelper = new PlaisioXmlHelper();
    [$class, $path] = $xmlHelper->queryExceptionHandlerClass();

    $generator = new ExceptionHandlerCodeGenerator();
    $code      = $generator->generateCode($class, $handlers);

    $writer = new TwoPhaseWrite($this->io);
    $writer->write($path, $code);

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
