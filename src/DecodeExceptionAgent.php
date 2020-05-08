<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Kernel\Nub;
use Plaisio\Obfuscator\Exception\DecodeException;
use Plaisio\Response\BadRequestResponse;

/**
 * An agent that handles DecodeException exceptions.
 */
class DecodeExceptionAgent
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
