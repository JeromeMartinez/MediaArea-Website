<?php

namespace UserBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UserBundle\Entity\User;
use UserBundle\Lib\ApiKey\ApiKeyManager;

class ApiKeyUserProvider implements UserProviderInterface
{
    protected $apiKeyManager;

    public function __construct(ApiKeyManager $apiKeyManager)
    {
        $this->apiKeyManager = $apiKeyManager;
    }

    public function getUserForApiKey($apiKey)
    {
        return $this->apiKeyManager->getUserForApiKey($apiKey);
    }

    /**
     * The identifier is the API key.
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->getUserForApiKey($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('API Key "%s" does not exist.', $identifier));
        }

        return $user;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
