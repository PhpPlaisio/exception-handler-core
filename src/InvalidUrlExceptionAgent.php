<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\InvalidUrlException;
use Plaisio\PlaisioObject;
use Plaisio\Response\NotFoundResponse;
use Plaisio\Response\Response;

/**
 * An agent that handles InvalidUrlException exceptions.
 */
class InvalidUrlExceptionAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a InvalidUrlException thrown in the constructor of a page object.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(InvalidUrlException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an InvalidUrlException thrown during generating the response by a page object.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(InvalidUrlException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an InvalidUrlException thrown during generating the response by a page object.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(InvalidUrlException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an InvalidUrlException.
   *
   * @param InvalidUrlException $exception The exception.
   *
   * @return Response
   */
  private function handleException(InvalidUrlException $exception): Response
  {
    $this->nub->DL->rollback();

    // Set the HTTP status to 404 (Not Found).
    $response = new NotFoundResponse();

    // Log the invalid request request.
    $this->nub->requestLogger->logRequest($response->getStatus());
    $this->nub->DL->commit();

    // Only on development environment log the error.
    if ($this->nub->request->isEnvDev)
    {
      $this->nub->errorLogger->logError($exception);
    }

    return $response;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
