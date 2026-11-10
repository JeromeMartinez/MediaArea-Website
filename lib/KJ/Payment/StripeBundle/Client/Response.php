<?php

namespace KJ\Payment\StripeBundle\Client;

use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Stripe\Exception\ApiErrorException as Error;

class Response
{
    protected $response;
    protected $error;
    protected $errorMessage;
    protected $errorResponseCode;
    protected $errorReasonCode;


    public function __construct($response, $error = null)
    {
        $this->response = $response;
        $this->error = $error;

        if ($error instanceof Error) {
            $body = $error->getJsonBody();
            $err = $body['error'];

            $this->errorMessage = $error->getMessage();
            $this->errorResponseCode = $err['type'];

            if (array_key_exists('code', $err) && !empty($err['code'])) {
                $this->errorReasonCode = $err['code'];
            } else {
                $this->errorReasonCode  = PluginInterface::REASON_CODE_INVALID;
            }
        } elseif ($error instanceof \Exception) {
            $this->errorMessage = $error->getMessage();
            $this->errorResponseCode = $error->getCode();
            $this->errorReasonCode = PluginInterface::REASON_CODE_INVALID;
        }
    }

    public function isSuccess()
    {
        return !$this->error;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorResponseCode()
    {
        return $this->errorResponseCode;
    }

    public function getErrorReasonCode()
    {
        return $this->errorReasonCode;
    }
} 
