<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\BadRequestException;
use Plaisio\PlaisioObject;
use Plaisio\Response\BadRequestResponse;
use Plaisio\Response\Response;

/**
 * An agent that handles BadRequestException exceptions.
 */
class BadRequestExceptionAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException thrown in the constructor of a page object.
   *
   * @param BadRequestException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(BadRequestException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException thrown during the preparation phase.
   *
   * @param BadRequestException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(BadRequestException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException thrown during generating the response by a page object.
   *
   * @param BadRequestException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(BadRequestException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a BadRequestException.
   *
   * @param BadRequestException $exception The exception.
   *
   * @return Response
   */
  private function handleException(BadRequestException $exception): Response
  {
    $this->nub->DL->rollback();

    // Set the HTTP status to 400 (Bad Request).
    $response = new BadRequestResponse();

    // Log the bad request.
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
