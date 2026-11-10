<?php

namespace UserBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UserBundle\Entity\User;

/**
 * Refresh the roles of the authenticated user at each request.
 *
 * The roles of a security token are the ones of the user when they logged in: a role given or removed
 * afterwards (registration of a guest, change made by an administrator...) would only apply at the next login.
 */
class RolesRefreshSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Just after the firewall
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        $roles = $user->getRoles();
        $tokenRoles = $token->getRoleNames();
        if (!array_diff($roles, $tokenRoles) && !array_diff($tokenRoles, $roles)) {
            return;
        }

        if ($token instanceof RememberMeToken) {
            $newToken = new RememberMeToken($user, $token->getFirewallName());
        } elseif ($token instanceof UsernamePasswordToken) {
            $newToken = new UsernamePasswordToken($user, $token->getFirewallName(), $roles);
        } else {
            return;
        }

        $newToken->setAttributes($token->getAttributes());
        $this->tokenStorage->setToken($newToken);
    }
}
