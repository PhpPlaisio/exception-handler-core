<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler\Helper;

use Plaisio\Console\Helper\PlaisioXmlHelper as BasePlaisioXmlHelper;

/**
 * Helper class for retrieving information about plaisio.xml files.
 */
class PlaisioXmlHelper extends BasePlaisioXmlHelper
{
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
  public function queryExceptionHandlerClass(): array
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
