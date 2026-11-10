<?php

namespace UserBundle\Lib\ApiKey;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use UserBundle\Entity\ApiKey;
use UserBundle\Lib\TokenGenerator;

class ApiKeyManager
{
    protected $em;
    protected $requestStack;
    protected $tokenGenerator;
    protected $passwordHasher;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $requestStack,
        TokenGenerator $tokenGenerator,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordHasher = $passwordHasher;
    }

    public function getUserForApiKey($apiKey)
    {
        $apiToken = $this->em->getRepository(\UserBundle\Entity\ApiKey::class)->findOneByToken($apiKey);

        if (null === $apiToken) {
            return null;
        }

        $user = $apiToken->getUser();
        $user->addRole('ROLE_API');

        return $user;
    }

    /**
     * Create ApiKey for a user.
     *
     * @param \UserBundle\Entity\User $user
     * @param string                  $app
     * @param string                  $version
     *
     * @return ApiKey
     */
    public function getApiKeyForUser($username, $password, $app = null, $version = null)
    {
        $user = $this->em->getRepository(\UserBundle\Entity\User::class)->findOneByUsername($username);

        if (!$user) {
            return null;
        }

        if (!$this->checkPassword($user, $password)) {
            return null;
        }

        $apiToken = $this->em->getRepository(\UserBundle\Entity\ApiKey::class)->findOneBy(['user' => $user, 'app' => $app]);

        if (!$apiToken) {
            $apiToken = $this->createApiKey($user, $app, $version);
        }

        return $apiToken;
    }

    /**
     * Create ApiKey for a user.
     *
     * @param \UserBundle\Entity\User $user
     * @param string                  $app
     * @param string                  $version
     *
     * @return ApiKey
     */
    protected function createApiKey($user, $app = null, $version = null)
    {
        $apiKey = new ApiKey();
        $apiKey->setUser($user)
            ->setApp($app)
            ->setVersion($version)
            ->setIp($this->requestStack->getCurrentRequest()->getClientIp())
            ->setToken($this->tokenGenerator->generateToken());

        $this->em->persist($apiKey);
        $this->em->flush();

        return $apiKey;
    }

    /**
     * Check if a password is valid.
     *
     * @param \UserBundle\Entity\User $user
     * @param string                  $password
     *
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return $this->passwordHasher->isPasswordValid($user, (string) $password);
    }
}
