<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Exception\InvalidUrlException;
use SetBased\Abc\Helper\HttpHeader;

/**
 * An agent that handles InvalidUrlException exceptions.
 */
class InvalidUrlExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an InvalidUrlException thrown during generating the response by a page object.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(InvalidUrlException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an InvalidUrlException thrown during generating the response by a page object.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(InvalidUrlException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an InvalidUrlException.
   *
   * @param InvalidUrlException $exception The exception.
   */
  private function handleException(InvalidUrlException $exception): void
  {
    Abc::$DL->rollback();

    // Set the HTTP status to 404 (Not Found).
    HttpHeader::clientErrorNotFound();

    // Log the invalid request request.
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
