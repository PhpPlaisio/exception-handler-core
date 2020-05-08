<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\BadRequestException;
use Plaisio\Kernel\Nub;
use Plaisio\Response\BadRequestResponse;

/**
 * An agent that handles BadRequestException exceptions.
 */
class BadRequestExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException thrown in the constructor of a page object.
   *
   * @param BadRequestException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(BadRequestException $exception): void
  {
    $this->handleException($exception);
  }

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
    $this->handleException($exception);
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
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException.
   *
   * @param BadRequestException $exception The exception.
   */
  private function handleException(BadRequestException $exception): void
  {
    Nub::$nub->DL->rollback();

    // Set the HTTP status to 400 (Bad Request).
    $response = new BadRequestResponse();
    $response->send();

    // Log the bad request.
    Nub::$nub->requestLogger->logRequest($response->getStatus());
    Nub::$nub->DL->commit();

    // Only on development environment log the error.
    if (Nub::$nub->request->isEnvDev())
    {
      Nub::$nub->errorLogger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
