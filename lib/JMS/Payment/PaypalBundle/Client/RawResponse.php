<?php

namespace JMS\Payment\PaypalBundle\Client;

/**
 * HTTP response of the Paypal API, as received.
 *
 * It replaces the response class of symfony/browser-kit, a testing component which was used here before.
 */
class RawResponse
{
    private $content;
    private $status;
    private $headers;

    public function __construct($content, $status, array $headers = array())
    {
        $this->content = (string) $content;
        $this->status = (int) $status;
        $this->headers = $headers;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
