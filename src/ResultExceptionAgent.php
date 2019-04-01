<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Exception\InvalidUrlException;
use SetBased\Stratum\Exception\ResultException;

/**
 * An agent that handles ResultException exceptions.
 */
class ResultExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a ResultException thrown in the constructor of a page object.
   *
   * @param ResultException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(ResultException $exception): void
  {
    // On a development environment rethrow the exception.
    if (Abc::$request->isEnvDev()) throw $exception;

    // A ResultException during the construction of a page object is (almost) always caused by an invalid URL.
    throw new InvalidUrlException([$exception], 'No data found');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
