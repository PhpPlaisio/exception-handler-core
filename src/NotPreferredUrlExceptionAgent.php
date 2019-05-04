<?php
declare(strict_types=1);

namespace SetBased\Abc\ExceptionHandler;

use SetBased\Abc\Abc;
use SetBased\Abc\Exception\NotPreferredUrlException;
use SetBased\Abc\Helper\HttpHeader;

/**
 * An agent that handles NotPreferredUrlException exceptions.
 */
class NotPreferredUrlExceptionAgent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an NotPreferredUrlException thrown during generating the response by a page object.
   *
   * @param NotPreferredUrlException $exception The exception.
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(NotPreferredUrlException $exception): void
  {
    Abc::$DL->rollback();

    // Redirect the user agent to the preferred URL.
    HttpHeader::redirectMovedPermanently($exception->preferredUri);

    // Log the not preferred request.
    Abc::$requestLogger->logRequest(HttpHeader::$status);
    Abc::$DL->commit();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
