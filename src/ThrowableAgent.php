<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Helper\HttpHeader;

/**
 * An agent that handles \Throwable exceptions.
 */
class ThrowableAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown in the constructor of a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @throws \Throwable
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(\Throwable $throwable): void
  {
    throw $throwable;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown during the preparation phase.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @throws \Throwable
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(\Throwable $throwable): void
  {
    throw $throwable;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown during generating the response by a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(\Throwable $throwable): void
  {
    Abc::$DL->rollback();

    // Set the HTTP status to 500 (Internal Server Error).
    HttpHeader::serverErrorInternalServerError();

    // Log the Internal Server Error
    Abc::$requestLogger->logRequest(HttpHeader::$status);
    Abc::$DL->commit();

    $logger = Abc::$abc->getErrorLogger();
    $logger->logError($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown during finalizing the response.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @since 1.0.0
   * @api
   */
  public function handleFinalizeException(\Throwable $throwable): void
  {
    $logger = Abc::$abc->getErrorLogger();
    $logger->logError($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
