<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\PlaisioObject;
use Plaisio\Response\InternalServerErrorResponse;
use Plaisio\Response\Response;

/**
 * An agent that handles \Throwable exceptions.
 */
class ThrowableAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown in the constructor of a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(\Throwable $throwable): Response
  {
    return $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable thrown during finalizing the response.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleFinalizeException(\Throwable $throwable): Response
  {
    // Try to rollback the database transaction.
    try
    {
      $this->nub->DL->rollback();
    }
    catch (\Throwable $exception)
    {
      // Nothing to do.
    }

    $this->nub->errorLogger->logError($throwable);

    // Set the HTTP status to 500 (Internal Server Error).
    return new InternalServerErrorResponse();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown during the preparation phase.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(\Throwable $throwable): Response
  {
    return $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable thrown during generating the response by a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(\Throwable $throwable): Response
  {
    return $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable.
   *
   * @return Response
   *
   * @param \Throwable $throwable The throwable.
   */
  private function handleException(\Throwable $throwable): Response
  {
    $this->nub->DL->rollback();

    // Set the HTTP status to 500 (Internal Server Error).
    $response = new InternalServerErrorResponse();

    // Log the Internal Server Error
    $this->nub->requestLogger->logRequest($response->getStatus());
    $this->nub->DL->commit();

    $this->nub->errorLogger->logError($throwable);

    return $response;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
