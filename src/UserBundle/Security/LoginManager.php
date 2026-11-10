<?php

namespace UserBundle\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use UserBundle\Entity\User;

/**
 * Log in a user without credentials (after a registration, for guests...).
 *
 * Derived from the login manager of FOSUserBundle (MIT license).
 */
class LoginManager
{
    private $tokenStorage;
    private $userChecker;
    private $sessionStrategy;
    private $requestStack;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        RequestStack $requestStack
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccountStatusException if the user can not be logged in
     */
    public function logInUser(string $firewallName, User $user): void
    {
        $this->userChecker->checkPreAuth($user);

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);
        }

        $this->tokenStorage->setToken($token);
    }
}
