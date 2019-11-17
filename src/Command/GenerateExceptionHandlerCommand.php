<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Command;

use Composer\IO\ConsoleIO;
use Plaisio\Console\Style\PlaisioStyle;
use Plaisio\ExceptionHandler\Helper\ExceptionHandlerCodeGenerator;
use Plaisio\ExceptionHandler\Helper\ExceptionHandlerMetadataExtractor;
use Plaisio\ExceptionHandler\Helper\PlaisioXmlHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generation the code for the core's exception handler.
 */
class GenerateExceptionHandlerCommand extends Command
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The Console IO object.
   *
   * @var ConsoleIO
   */
  private $consoleIo;

  /**
   * The output decorator.
   *
   * @var PlaisioStyle
   */
  private $io;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function configure()
  {
    $this->setName('plaisio:generate-core-exception-handler')
         ->setDescription('Generates the code for the core\'s exception handler')
         ->addArgument('config file', InputArgument::OPTIONAL, 'The abc.xml configuration file', 'abc.xml');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->io        = new PlaisioStyle($input, $output);
    $this->consoleIo = new ConsoleIO($input, $output, $this->getHelperSet());

    $metadataExtractor = new ExceptionHandlerMetadataExtractor($this->io, $input->getArgument('config file'));
    $handlers          = $metadataExtractor->extractExceptionAgents();

    $xmlHelper = new PlaisioXmlHelper($input->getArgument('config file'));
    [$class, $path] = $xmlHelper->extractExceptionHandlerClass();

    $generator = new ExceptionHandlerCodeGenerator();
    $code      = $generator->generateCode($class, $handlers);

    $this->writeTwoPhases($path, $code);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes a file in two phase to the filesystem.
   *
   * First write the data to a temporary file (in the same directory) and than renames the temporary file. If the file
   * already exists and its content is equal to the data that must be written no action  is taken. This has the
   * following advantages:
   * <ul>
   * <li> In case of some write error (e.g. disk full) the original file is kept in tact and no file with partially data
   *      is written.
   * <li> Renaming a file is atomic. So, running processes will never read a partially written data.
   * </ul>
   *
   * @param string $filename The name of the file were the data must be stored.
   * @param string $data     The data that must be written.
   */
  private function writeTwoPhases(string $filename, string $data): void
  {
    $write_flag = true;
    if (file_exists($filename))
    {
      $old_data = file_get_contents($filename);
      if ($data==$old_data) $write_flag = false;
    }

    if ($write_flag)
    {
      $tmp_filename = $filename.'.tmp';
      file_put_contents($tmp_filename, $data);
      rename($tmp_filename, $filename);

      $this->io->writeln(sprintf('Wrote <fso>%s</fso>', OutputFormatter::escape($filename)));
    }
    else
    {
      $this->io->writeln(sprintf('File <fso>%s</fso> is up to date', OutputFormatter::escape($filename)));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
