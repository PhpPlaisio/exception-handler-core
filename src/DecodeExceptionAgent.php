<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Obfuscator\Exception\DecodeException;
use Plaisio\PlaisioObject;
use Plaisio\Response\BadRequestResponse;

/**
 * An agent that handles DecodeException exceptions.
 */
class DecodeExceptionAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a DecodeException thrown in the constructor of a page object.
   *
   * @param DecodeException $exception The exception.
   *
   * @since 1.2.0
   * @api
   */
  public function handleConstructException(DecodeException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a DecodeException thrown thrown during the preparation phase.
   *
   * @param DecodeException $exception The exception.
   *
   * @since 1.2.0
   * @api
   */
  public function handlePrepareException(DecodeException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a DecodeException thrown during generating the response by a page object.
   *
   * @param DecodeException $exception The exception.
   *
   * @since 1.2.0
   * @api
   */
  public function handleResponseException(DecodeException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a DecodeException.
   *
   * @param DecodeException $exception The exception.
   */
  private function handleException(DecodeException $exception): void
  {
    $this->nub->DL->rollback();

    // Set the HTTP status to 400 (Bad Request).
    $response = new BadRequestResponse();
    $response->send();

    // Log the bad request.
    $this->nub->requestLogger->logRequest($response->getStatus());
    $this->nub->DL->commit();

    // Only on development environment log the error.
    if ($this->nub->request->isEnvDev())
    {
      $this->nub->errorLogger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
