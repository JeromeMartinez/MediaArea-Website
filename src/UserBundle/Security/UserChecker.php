<?php

namespace UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserBundle\Entity\User;

/**
 * Refuse the disabled accounts.
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && !$user->isEnabled()) {
            $exception = new DisabledException('User account is disabled.');
            $exception->setUser($user);

            throw $exception;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
