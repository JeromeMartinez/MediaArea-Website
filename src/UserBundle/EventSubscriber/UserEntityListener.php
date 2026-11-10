<?php

namespace UserBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use UserBundle\Entity\User;
use UserBundle\Lib\UserManager;

/**
 * Keep the canonical fields and the password hash up to date, whatever saves the user (e.g. the admin).
 *
 * Derived from the Doctrine user listener of FOSUserBundle (MIT license).
 */
#[AsEntityListener(event: Events::prePersist, entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, entity: User::class)]
class UserEntityListener
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function prePersist(User $user): void
    {
        $this->updateUserFields($user);
    }

    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        $this->updateUserFields($user);

        $em = $args->getObjectManager();
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata(User::class), $user);
    }

    private function updateUserFields(User $user): void
    {
        $this->userManager->updateCanonicalFields($user);
        $this->userManager->updatePassword($user);
    }
}
