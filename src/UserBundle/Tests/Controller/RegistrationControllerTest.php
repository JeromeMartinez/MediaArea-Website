<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Lib\TokenGenerator;
use UserBundle\Lib\UserManager;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterNotConnected()
    {
        $client = static::createClient();

        $client->request('GET', '/register/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRegisterFormWithoutUsername()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register/');
        $form = $crawler->selectButton('Register')->form();

        $email = 'usertest@mediaarea.net';
        $pass = static::getContainer()->get(TokenGenerator::class)->generateToken();
        $userManager = static::getContainer()->get(UserManager::class);

        $form->setValues(array(
            'fos_user_registration_form[email]' => $email,
            'fos_user_registration_form[plainPassword][first]' => $pass,
            'fos_user_registration_form[plainPassword][second]' => $pass,
        ));
        $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/register/confirmed'));

        $user = $userManager->findUserByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotNull($user->getUsername());
        $this->assertEquals(0, $user->getRealUserName());
        $userManager->deleteUser($user);
    }

    public function testRegisterFormWithUsername()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register/');
        $form = $crawler->selectButton('Register')->form();

        $email = 'usertest@mediaarea.net';
        $username = 'usertest';
        $pass = static::getContainer()->get(TokenGenerator::class)->generateToken();
        $userManager = static::getContainer()->get(UserManager::class);

        $form->setValues(array(
            'fos_user_registration_form[email]' => $email,
            'fos_user_registration_form[plainPassword][first]' => $pass,
            'fos_user_registration_form[plainPassword][second]' => $pass,
            'fos_user_registration_form[username]' => $username,
        ));
        $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/register/confirmed'));

        $user = $userManager->findUserByEmail($email);
        $this->assertNotNull($user);
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals(1, $user->getRealUserName());
        $this->assertEquals(1, $user->getNewsletter());
        $userManager->deleteUser($user);
    }
}
