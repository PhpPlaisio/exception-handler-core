<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Exception\NotPreferredUrlException;
use Plaisio\PlaisioObject;
use Plaisio\Response\MovedPermanentlyResponse;
use Plaisio\Response\Response;

/**
 * An agent that handles NotPreferredUrlException exceptions.
 */
class NotPreferredUrlExceptionAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NotPreferredUrlException thrown during generating the response by a page object.
   *
   * @param NotPreferredUrlException $exception The exception.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(NotPreferredUrlException $exception): Response
  {
    $this->nub->DL->rollback();

    // Redirect the user agent to the preferred URL.
    $response = new MovedPermanentlyResponse($exception->preferredUri);

    // Log the not preferred request.
    $this->nub->requestLogger->logRequest($response->getStatus());
    $this->nub->DL->commit();

    return $response;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
