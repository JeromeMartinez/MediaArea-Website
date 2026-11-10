<?php

namespace UserBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UserBundle\Entity\User;
use UserBundle\Lib\UserManager;

/**
 * Load users by username or email.
 */
class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userManager->findUserByUsernameOrEmail($identifier);

        if (!$user) {
            $exception = new UserNotFoundException(sprintf('Username "%s" does not exist.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', User::class, get_class($user)));
        }

        if (null === $reloadedUser = $this->userManager->findUserBy(['id' => $user->getId()])) {
            throw new UserNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Store the password hashed with the current algorithm, called after a successful login when needed.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            return;
        }

        $user->setSalt(null);
        $user->setPassword($newHashedPassword);
        $this->userManager->updateUser($user);
    }
}
