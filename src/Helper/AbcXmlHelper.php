<?php

namespace SetBased\Abc\ExceptionHandler\Helper;

use SetBased\Exception\RuntimeException;

/**
 * Helper class for retrieving information about abc.xml files.
 */
class AbcXmlHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The path to the abc.xml file.
   *
   * @var string
   */
  private $path;

  /**
   * The XML of the abc.xml.
   *
   * @var \DOMDocument
   */
  private $xml;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * AbcXmlHelper constructor.
   *
   * @param string $path The path to the abc.xml file.
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
   * Returns the exception agents found in the abc.xml file.
   *
   * @return string[]
   */
  public function extractExceptionAgents(): array
  {
    $classes = [];

    $xpath = new \DOMXpath($this->xml);
    $list  = $xpath->query('/abc/exception/agents/agent');
    foreach ($list as $item)
    {
      $classes[] = $item->nodeValue;
    }

    return $classes;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the class name and the path of he generated exception handler.
   *
   * @return string[]
   */
  public function extractExceptionHandlerClass(): array
  {
    $xpath = new \DOMXpath($this->xml);

    $list  = $xpath->query('/abc/exception/class');
    $class = $list[0]->nodeValue;

    $list  = $xpath->query('/abc/exception/path');
    $path = $list[0]->nodeValue;

    return [$class, $path];
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
