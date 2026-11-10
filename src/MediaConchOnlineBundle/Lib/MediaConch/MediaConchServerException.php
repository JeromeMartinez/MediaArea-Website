<?php

namespace MediaConchOnlineBundle\Lib\MediaConch;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MediaConchServerException extends HttpException
{
    public function __construct($message = null, $statusCode = 400, ?\Exception $previous = null, $code = 0)
    {
        parent::__construct((int) $statusCode, (string) $message, $previous, [], (int) $code);
    }
}
