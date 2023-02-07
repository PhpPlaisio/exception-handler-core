<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Helper;

/**
 * Helper class for retrieving information about plaisio.xml files.
 */
class PlaisioXmlQueryHelper extends \Plaisio\Console\Helper\PlaisioXmlQueryHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the exception agents found in the plaisio.xml file.
   *
   * @return string[]
   */
  public function queryExceptionAgents(): array
  {
    $classes = [];

    $xpath = new \DOMXpath($this->xml);
    $list  = $xpath->query('/exception/agents/agent');
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
  public function queryExceptionHandlerClass(): array
  {
    $xpath = new \DOMXpath($this->xml);

    $list  = $xpath->query('/exception/class');
    $class = $list[0]->nodeValue;

    $list = $xpath->query('/exception/path');
    $path = $list[0]->nodeValue;

    return [$class, $path];
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
