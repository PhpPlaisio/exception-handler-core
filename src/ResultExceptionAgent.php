<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Helper\HttpHeader;
use SetBased\Stratum\Middle\Exception\ResultException;

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
    Abc::$DL->rollback();

    // Set the HTTP status to 404 (Not Found).
    HttpHeader::clientErrorNotFound();

    // Log the invalid URL request.
    Abc::$requestLogger->logRequest(HttpHeader::$status);
    Abc::$DL->commit();

    // On a development environment log the exception.
    if (Abc::$request->isEnvDev())
    {
      $logger = Abc::$abc->getErrorLogger();
      $logger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
