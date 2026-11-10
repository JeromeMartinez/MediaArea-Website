<?php

// ...

namespace MediaConchOnlineBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Lib\UserManager;

abstract class AbstractAuthControllerTestCase extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client = null;

    public function setUp(): void
    {
        $this->client = $this->createAuthorizedClient();
    }

    /**
     * @return KernelBrowser
     */
    protected function createAuthorizedClient()
    {
        // A test may already have created a client
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = static::getContainer();

        $user = $container->get(UserManager::class)->findUserBy(['username' => 'beta']);
        $client->loginUser($user, $container->getParameter('user.firewall_name'));

        return $client;
    }
}
