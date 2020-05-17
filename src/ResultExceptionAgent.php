<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\PlaisioObject;
use Plaisio\Response\NotFoundResponse;
use SetBased\Stratum\Middle\Exception\ResultException;

/**
 * An agent that handles ResultException exceptions.
 */
class ResultExceptionAgent extends PlaisioObject
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
    $this->nub->DL->rollback();

    // Set the HTTP status to 404 (Not Found).
    $response = new NotFoundResponse();
    $response->send();

    // Log the invalid URL request.
    $this->nub->requestLogger->logRequest($response->getStatus());
    $this->nub->DL->commit();

    // On a development environment log the exception.
    if ($this->nub->request->isEnvDev())
    {
      $this->nub->errorLogger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
