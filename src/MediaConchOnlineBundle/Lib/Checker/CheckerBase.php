<?php

namespace MediaConchOnlineBundle\Lib\Checker;

use UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use MediaConchOnlineBundle\Lib\MediaConch\MediaConchServer;

class CheckerBase
{
    protected $mc;
    protected $response;
    protected $user;

    public function __construct(MediaConchServer $mc, TokenStorageInterface $tokenStorage)
    {
        $this->mc = $mc;

        $token = $tokenStorage->getToken();
        if (null !== $token && $token->getUser() instanceof User) {
            $this->user = $token->getUser();
        } else {
            throw new \Exception('Invalid User');
        }
    }

    public function getResponse()
    {
        return $this->response;
    }
}
