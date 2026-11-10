<?php

namespace UserBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use UserBundle\Entity\User;
use UserBundle\Lib\UserManager;

/**
 * Keep the date of the last login of the users.
 */
class LastLoginSubscriber implements EventSubscriberInterface
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime());
            $this->userManager->updateUser($user);
        }
    }
}
