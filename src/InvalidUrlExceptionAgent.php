<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\InvalidUrlException;
use Plaisio\Kernel\Nub;
use Plaisio\Response\NotFoundResponse;

/**
 * An agent that handles InvalidUrlException exceptions.
 */
class InvalidUrlExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a InvalidUrlException thrown in the constructor of a page object.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(InvalidUrlException $exception): void
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
    Nub::$DL->rollback();

    // Set the HTTP status to 404 (Not Found).
    $response = new NotFoundResponse();
    $response->send();

    // Log the invalid request request.
    Nub::$requestLogger->logRequest($response->getStatus());
    Nub::$DL->commit();

    // Only on development environment log the error.
    if (Nub::$request->isEnvDev())
    {
      $logger = Nub::$nub->getErrorLogger();
      $logger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
