<?php

namespace UserBundle\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use UserBundle\Entity\User;

/**
 * Create, find and save users.
 *
 * Derived from the user manager of FOSUserBundle (MIT license).
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UserManager
{
    private $em;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Returns an empty user instance.
     */
    public function createUser(): User
    {
        return new User();
    }

    public function deleteUser(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function findUserBy(array $criteria): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy($criteria);
    }

    public function findUserByEmail($email): ?User
    {
        return $this->findUserBy(['emailCanonical' => self::canonicalize($email)]);
    }

    public function findUserByUsername($username): ?User
    {
        return $this->findUserBy(['usernameCanonical' => self::canonicalize($username)]);
    }

    public function findUserByUsernameOrEmail($usernameOrEmail): ?User
    {
        if (preg_match('/^.+\@\S+\.\S+$/', (string) $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Updates the canonical fields and the password of a user then saves it.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function updateUser(User $user, bool $andFlush = true): void
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->em->persist($user);
        if ($andFlush) {
            $this->em->flush();
        }
    }

    public function updateCanonicalFields(User $user): void
    {
        $user->setUsernameCanonical(self::canonicalize($user->getUsername()));
        $user->setEmailCanonical(self::canonicalize($user->getEmail()));
    }

    /**
     * Hashes the plain password of a user, if any.
     */
    public function updatePassword(User $user): void
    {
        $plainPassword = (string) $user->getPlainPassword();

        if ('' === $plainPassword) {
            return;
        }

        // The hash carries its own salt
        $user->setSalt(null);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->eraseCredentials();
    }

    public static function canonicalize($string): ?string
    {
        if (null === $string) {
            return null;
        }

        $encoding = mb_detect_encoding($string);

        return $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);
    }
}
