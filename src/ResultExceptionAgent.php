<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Kernel\Nub;
use Plaisio\Response\NotFoundResponse;
use SetBased\Stratum\Middle\Exception\ResultException;

/**
 * An agent that handles ResultException exceptions.
 */
class ResultExceptionAgent
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
    Nub::$DL->rollback();

    // Set the HTTP status to 404 (Not Found).
    $response = new NotFoundResponse();
    $response->send();

    // Log the invalid URL request.
    Nub::$requestLogger->logRequest($response->getStatus());
    Nub::$DL->commit();

    // On a development environment log the exception.
    if (Nub::$request->isEnvDev())
    {
      $logger = Nub::$nub->getErrorLogger();
      $logger->logError($exception);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
