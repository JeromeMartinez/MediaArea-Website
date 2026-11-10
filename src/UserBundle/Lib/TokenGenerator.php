<?php

namespace UserBundle\Lib;

class TokenGenerator
{
    /**
     * Random URL safe token.
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
