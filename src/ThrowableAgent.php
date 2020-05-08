<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Kernel\Nub;
use Plaisio\Response\InternalServerErrorResponse;

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
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(\Throwable $throwable): void
  {
    $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable thrown during finalizing the response.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @since 1.0.0
   * @api
   */
  public function handleFinalizeException(\Throwable $throwable): void
  {
    Nub::$nub->DL->rollback();

    Nub::$nub->errorLogger->logError($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown during the preparation phase.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(\Throwable $throwable): void
  {
    $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable thrown during generating the response by a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(\Throwable $throwable): void
  {
    $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable.
   *
   * @param \Throwable $throwable The throwable.
   */
  private function handleException(\Throwable $throwable): void
  {
    Nub::$nub->DL->rollback();

    // Set the HTTP status to 500 (Internal Server Error).
    $response = new InternalServerErrorResponse();
    $response->send();

    // Log the Internal Server Error
    Nub::$nub->requestLogger->logRequest($response->getStatus());
    Nub::$nub->DL->commit();

    Nub::$nub->errorLogger->logError($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
