<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\NotAuthorizedException;
use Plaisio\Kernel\Nub;
use Plaisio\Response\NotFoundResponse;
use Plaisio\Response\SeeOtherResponse;

/**
 * An agent that handles NotAuthorizedException exceptions.
 */
class NotAuthorizedExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown in the constructor of a page object.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(NotAuthorizedException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown during the preparation phase.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(NotAuthorizedException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException thrown during generating the response by a page object.
   *
   * @param NotAuthorizedException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(NotAuthorizedException $exception): void
  {
    $this->handleException($exception);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotAuthorizedException.
   *
   * @param NotAuthorizedException $exception The exception.
   */
  private function handleException(NotAuthorizedException $exception): void
  {
    Nub::$nub->DL->rollback();

    if (Nub::$nub->session->isAnonymous())
    {
      // The user is not logged on and most likely the user has requested a page for which the user must be logged on.

      // Redirect the user agent to the login page. After the user has successfully logged on the user agent will be
      // redirected to currently requested URL.
      $response = new SeeOtherResponse(Nub::$nub->getLoginUrl(Nub::$nub->request->getRequestUri()));
      $response->send();
    }
    else
    {
      // The user is logged on and the user has requested an URL for which the user has no authorization.

      // Set the HTTP status to 404 (Not Found).
      $response = new NotFoundResponse();
      $response->send();

      // Only on development environment log the error.
      if (Nub::$nub->request->isEnvDev())
      {
        Nub::$nub->errorLogger->logError($exception);
      }
    }

    // Log the not authorized request.
    Nub::$nub->requestLogger->logRequest($response->getStatus());
    Nub::$nub->DL->commit();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
