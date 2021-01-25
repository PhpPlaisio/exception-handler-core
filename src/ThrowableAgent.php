<?php
declare(strict_types=1);

namespace Plaisio\ExceptionHandler;

use Plaisio\Helper\OB;
use Plaisio\PlaisioObject;
use Plaisio\Response\BaseResponse;
use Plaisio\Response\HtmlResponse;
use Plaisio\Response\InternalServerErrorResponse;
use Plaisio\Response\Response;

/**
 * An agent that handles \Throwable exceptions.
 */
class ThrowableAgent extends PlaisioObject
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown in the constructor of a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleConstructException(\Throwable $throwable): Response
  {
    return $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable thrown during finalizing the response.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleFinalizeException(\Throwable $throwable): Response
  {
    try
    {
      $this->nub->DL->rollback();
    }
    catch (\Throwable $e)
    {
      // Nothing to do.
    }

    return $this->errorLoggerResponse($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fallback exception handler for exceptions thrown during the preparation phase.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handlePrepareException(\Throwable $throwable): Response
  {
    return $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable thrown during generating the response by a page object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   *
   * @since 1.0.0
   * @api
   */
  public function handleResponseException(\Throwable $throwable): Response
  {
    return $this->handleException($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tries to log the error and returns the appropriate response object.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return HtmlResponse|InternalServerErrorResponse
   */
  private function errorLoggerResponse(\Throwable $throwable): Response
  {
    try
    {
      $ob = new OB();
      $this->nub->errorLogger->logError($throwable);
      $contents = $ob->getClean();
    }
    catch (\Throwable $e)
    {
      // Nothing to do.
    }

    if ($this->nub->request->isEnvDev() && isset($contents) && $contents!=='')
    {
      $response = new HtmlResponse($contents);
      $response->setStatus(BaseResponse::STATUS_CODE_INTERNAL_SERVER_ERROR);
    }
    else
    {
      $response = new InternalServerErrorResponse();
    }

    return $response;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a Throwable.
   *
   * @param \Throwable $throwable The throwable.
   *
   * @return Response
   */
  private function handleException(\Throwable $throwable): Response
  {
    try
    {
      $this->nub->DL->rollback();

      $this->nub->requestLogger->logRequest(BaseResponse::STATUS_CODE_INTERNAL_SERVER_ERROR);
      $this->nub->DL->commit();
    }
    catch (\Throwable $e)
    {
      // Noting to do.
    }

    return $this->errorLoggerResponse($throwable);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
