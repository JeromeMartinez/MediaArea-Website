<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Lib\UserManager;

/**
 * Class UserAbstractControllerTestCase.
 */
abstract class UserAbstractControllerTestCase extends WebTestCase
{
    /**
     * @return KernelBrowser
     */
    protected function createRegularUserClient()
    {
        return $this->createAuthorizedClient('test@mediaarea.net');
    }

    /**
     * @return KernelBrowser
     */
    protected function createBetaUserClient()
    {
        return $this->createAuthorizedClient('beta@mediaarea.net');
    }

    /**
     * @param  string $email Email of user to authenticate
     *
     * @return KernelBrowser
     */
    private function createAuthorizedClient($email)
    {
        // A test may already have created a client
        static::ensureKernelShutdown();
        $client = static::createClient();
        $container = static::getContainer();

        $user = $container->get(UserManager::class)->findUserBy(array('email' => $email));
        $client->loginUser($user, $container->getParameter('user.firewall_name'));

        return $client;
    }
}
