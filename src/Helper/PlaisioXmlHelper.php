<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Helper;

use SetBased\Exception\RuntimeException;

/**
 * Helper class for retrieving information about plaisio.xml files.
 */
class PlaisioXmlHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The path to the plaisio.xml file.
   *
   * @var string
   */
  private $path;

  /**
   * The XML of the plaisio.xml.
   *
   * @var \DOMDocument
   */
  private $xml;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * PlaisioXmlHelper constructor.
   *
   * @param string $path The path to the plaisio.xml file.
   */
  public function __construct(string $path)
  {
    $this->path = $path;

    $this->xml = new \DOMDocument();
    $success   = $this->xml->load($path, LIBXML_NOWARNING);
    if (!$success)
    {
      throw new RuntimeException('Unable to parse XML file "%s".', $path);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the exception agents found in the plaisio.xml file.
   *
   * @return string[]
   */
  public function extractExceptionAgents(): array
  {
    $classes = [];

    $xpath = new \DOMXpath($this->xml);
    $list  = $xpath->query('/plaisio/exception/agents/agent');
    foreach ($list as $item)
    {
      $classes[] = $item->nodeValue;
    }

    return $classes;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the class name and the path of the generated exception handler.
   *
   * @return string[]
   */
  public function extractExceptionHandlerClass(): array
  {
    $xpath = new \DOMXpath($this->xml);

    $list  = $xpath->query('/plaisio/exception/class');
    $class = $list[0]->nodeValue;

    $list = $xpath->query('/plaisio/exception/path');
    $path = $list[0]->nodeValue;

    return [$class, $path];
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------