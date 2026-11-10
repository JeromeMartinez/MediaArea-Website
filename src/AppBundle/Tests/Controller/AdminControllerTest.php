<?php

namespace Tests\AppBundle\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Entity\User;
use UserBundle\Lib\UserManager;

class AdminControllerTest extends WebTestCase
{
    public const ADMIN_EMAIL = 'admintest@mediaarea.net';

    protected function tearDown(): void
    {
        if (static::$booted) {
            $userManager = static::getContainer()->get(UserManager::class);
            $admin = $userManager->findUserByEmail(self::ADMIN_EMAIL);
            if (null !== $admin) {
                $userManager->deleteUser($admin);
            }
        }

        parent::tearDown();
    }

    public function testNotConnected()
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));

        $client->request('GET', '/admin/user');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testConnectedWithoutAdminRole()
    {
        $client = static::createClient();
        $user = static::getContainer()->get(UserManager::class)->findUserByEmail('test@mediaarea.net');
        $client->loginUser($user, static::getContainer()->getParameter('user.firewall_name'));

        $client->request('GET', '/admin');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->request('GET', '/admin/user');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testDashboard()
    {
        $client = $this->createAdminClient();
        $client->request('GET', '/admin');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/admin/user'));
    }

    #[DataProvider('urlProvider')]
    public function testResponse200($url)
    {
        $client = $this->createAdminClient();
        $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('MediaArea Admin', $client->getResponse()->getContent());
    }

    public static function urlProvider()
    {
        return [
            ['/admin/user'],
            ['/admin/user?query=admintest'],
            ['/admin/invoice'],
            ['/admin/feature'],
            ['/admin/feature/new'],
            ['/admin/feature/1'],
            ['/admin/feature/1/edit'],
            ['/admin/vote'],
            ['/admin/user-quotas'],
            ['/admin/user-quotas-default'],
            ['/admin/user-quotas-default/new'],
        ];
    }

    #[DataProvider('disabledUrlProvider')]
    public function testDisabledAction($url)
    {
        $client = $this->createAdminClient();
        $client->request('GET', $url);

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public static function disabledUrlProvider()
    {
        return [
            ['/admin/user/new'],
            ['/admin/invoice/new'],
            ['/admin/vote/new'],
            ['/admin/user-quotas/new'],
        ];
    }

    public function testUser()
    {
        $client = $this->createAdminClient();
        $admin = static::getContainer()->get(UserManager::class)->findUserByEmail(self::ADMIN_EMAIL);

        $crawler = $client->request('GET', '/admin/user/'.$admin->getId());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::ADMIN_EMAIL, $crawler->filter('.content')->text());

        $crawler = $client->request('GET', '/admin/user/'.$admin->getId().'/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Edit the user, with a language which is a regional variant
        $form = $crawler->filter('form[name="User"]')->form();
        $form['User[name]'] = 'Admin test';
        $form['User[country]'] = 'FR';
        $form['User[language]'] = 'en_US';
        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        static::getContainer()->get('doctrine')->getManager()->clear();
        $admin = static::getContainer()->get(UserManager::class)->findUserByEmail(self::ADMIN_EMAIL);
        $this->assertEquals('Admin test', $admin->getName());
        $this->assertEquals('FR', $admin->getCountry());
        $this->assertEquals('en_US', $admin->getLanguage());
        $this->assertTrue($admin->hasRole('ROLE_ADMIN'));
        $this->assertEquals('admintest', $admin->getUsername());
    }

    /**
     * @return KernelBrowser
     */
    private function createAdminClient()
    {
        $client = static::createClient();
        $userManager = static::getContainer()->get(UserManager::class);

        $admin = $userManager->createUser();
        $admin->setUsername('admintest');
        $admin->setEmail(self::ADMIN_EMAIL);
        $admin->setPlainPassword(bin2hex(random_bytes(16)));
        $admin->setEnabled(true);
        $admin->addRole('ROLE_ADMIN');
        $userManager->updateUser($admin);

        $client->loginUser($admin, static::getContainer()->getParameter('user.firewall_name'));

        return $client;
    }
}
