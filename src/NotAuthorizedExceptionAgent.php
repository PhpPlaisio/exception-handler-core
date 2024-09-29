<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\NotAuthorizedException;
use Plaisio\PlaisioObject;
use Plaisio\Response\NotFoundResponse;
use Plaisio\Response\Response;
use Plaisio\Response\SeeOtherResponse;

/**
 * An agent that handles NotAuthorizedException exceptions.
 */
class NotAuthorizedExceptionAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown in the constructor of a page object.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(NotAuthorizedException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown during the preparation phase.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(NotAuthorizedException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown during generating the response by a page object.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(NotAuthorizedException $exception): Response
  {
    return $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @return Response
   */
  private function handleException(NotAuthorizedException $exception): Response
  {
    $this->nub->DL->rollback();

    if ($this->nub->session->isAnonymous())
    {
      // The user is not logged on and most likely the user has requested a page for which the user must be logged on.

      // Redirect the user agent to the login page. After the user has successfully logged on the user agent will be
      // redirected to currently requested URL.
      $response = new SeeOtherResponse($this->nub->getLoginUrl($this->nub->request->requestUri));
    }
    else
    {
      // The user is logged on and the user has requested a URL for which the user has no authorization.

      // Set the HTTP status to 404 (Not Found).
      $response = new NotFoundResponse();

      // Only on development environment log the error.
      if ($this->nub->request->isEnvDev)
      {
        $this->nub->errorLogger->logError($exception);
      }
    }

    // Log the not authorized request.
    $this->nub->requestLogger->logRequest($response->getStatus());
    $this->nub->DL->commit();

    return $response;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
