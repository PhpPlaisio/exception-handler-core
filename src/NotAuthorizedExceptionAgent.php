<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Exception\NotAuthorizedException;
use SetBased\Abc\Helper\HttpHeader;

/**
 * An agent that handles NotAuthorizedException exceptions.
 */
class NotAuthorizedExceptionAgent
{
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
    Abc::$DL->rollback();

    if (Abc::$session->isAnonymous())
    {
      // The user is not logged on and most likely the user has requested a page for which the user must be logged on.

      // Redirect the user agent to the login page. After the user has successfully logged on the user agent will be
      // redirected to currently requested URL.
      HttpHeader::redirectSeeOther(Abc::$abc->getLoginUrl(Abc::$request->getRequestUri()));
    }
    else
    {
      // The user is logged on and the user has requested an URL for which the user has no authorization.

      // Set the HTTP status to 404 (Not Found).
      HttpHeader::clientErrorNotFound();

      // Only on development environment log the error.
      if (Abc::$request->isEnvDev())
      {
        $logger = Abc::$abc->getErrorLogger();
        $logger->logError($exception);
      }
    }

    // Log the not authorized request.
    Abc::$requestLogger->logRequest(HttpHeader::$status);
    Abc::$DL->commit();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
