<?php

namespace UserBundle\Lib;

use UserBundle\Entity\User;

/**
 * Easy creation and manipulation of users, mostly for the commands.
 *
 * Derived from the user manipulator of FOSUserBundle (MIT license).
 */
class UserManipulator
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function create($username, $password, $email, $active, $superadmin): User
    {
        $user = $this->userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((bool) $active);
        $user->setSuperAdmin((bool) $superadmin);
        $this->userManager->updateUser($user);

        return $user;
    }

    public function activate($username): void
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
    }

    public function deactivate($username): void
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setEnabled(false);
        $this->userManager->updateUser($user);
    }

    public function changePassword($username, $password): void
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setPlainPassword($password);
        $this->userManager->updateUser($user);
    }

    public function promote($username): void
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setSuperAdmin(true);
        $this->userManager->updateUser($user);
    }

    public function demote($username): void
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setSuperAdmin(false);
        $this->userManager->updateUser($user);
    }

    /**
     * @return bool true if the role was added, false if the user already had it
     */
    public function addRole($username, $role): bool
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        if ($user->hasRole($role)) {
            return false;
        }
        $user->addRole($role);
        $this->userManager->updateUser($user);

        return true;
    }

    /**
     * @return bool true if the role was removed, false if the user did not have it
     */
    public function removeRole($username, $role): bool
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        if (!$user->hasRole($role)) {
            return false;
        }
        $user->removeRole($role);
        $this->userManager->updateUser($user);

        return true;
    }

    private function findUserByUsernameOrThrowException($username): User
    {
        $user = $this->userManager->findUserByUsername($username);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        return $user;
    }
}
