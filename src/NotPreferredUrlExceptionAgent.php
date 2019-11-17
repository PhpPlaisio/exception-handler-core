<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\NotPreferredUrlException;
use Plaisio\Kernel\Nub;
use Plaisio\Response\MovedPermanentlyResponse;

/**
 * An agent that handles NotPreferredUrlException exceptions.
 */
class NotPreferredUrlExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotPreferredUrlException thrown during generating the response by a page object.
   *
   * @param NotPreferredUrlException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(NotPreferredUrlException $exception): void
  {
    Nub::$DL->rollback();

    // Redirect the user agent to the preferred URL.
    $response = new MovedPermanentlyResponse($exception->preferredUri);
    $response->send();

    // Log the not preferred request.
    Nub::$requestLogger->logRequest($response->getStatus());
    Nub::$DL->commit();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
