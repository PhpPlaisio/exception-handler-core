<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Exception\BadRequestException;
use SetBased\Abc\Helper\HttpHeader;

/**
 * An agent that handles BadRequestException exceptions.
 */
class BadRequestExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException thrown during the preparation phase.
   *
   * @param BadRequestException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(BadRequestException $exception): void
  {
    Abc::$DL->rollback();

    // Set the HTTP status to 400 (Bad Request).
    HttpHeader::clientErrorBadRequest();

    // Only on development environment log the error.
    if (Abc::$request->isEnvDev())
    {
      $logger = Abc::$abc->getErrorLogger();
      $logger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException thrown during generating the response by a page object.
   *
   * @param BadRequestException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(BadRequestException $exception): void
  {
    Abc::$DL->rollback();

    // Set the HTTP status to 400 (Bad Request).
    HttpHeader::clientErrorBadRequest();

    // Log the bad request.
    Abc::$requestLogger->logRequest(HttpHeader::$status);
    Abc::$DL->commit();

    // Only on development environment log the error.
    if (Abc::$request->isEnvDev())
    {
      $logger = Abc::$abc->getErrorLogger();
      $logger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
