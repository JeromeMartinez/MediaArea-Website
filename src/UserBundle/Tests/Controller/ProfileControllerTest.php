<?php

namespace UserBundle\Tests\Controller;

use UserBundle\Lib\UserManager;

class ProfileControllerTest extends UserAbstractControllerTestCase
{
    public function testProfileNotConnected()
    {
        $client = static::createClient();

        $client->request('GET', '/profile/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testProfileConnected()
    {
        $client = $this->createRegularUserClient();

        $crawler = $client->request('GET', '/profile/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'Email: test@mediaarea.net',
            trim($crawler->filter('.fos_user_user_show .panel-body p')->text())
        );
    }

    public function testProfileEdit()
    {
        $client = $this->createRegularUserClient();

        $crawler = $client->request('GET', '/profile/edit');
        $form = $crawler->selectButton('Update')->form();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'test@mediaarea.net',
            trim($form->get('fos_user_profile_form[email]')->getValue())
        );
    }

    public function testProfileEditSelectsCountryAndLanguage()
    {
        $client = $this->createRegularUserClient();
        $userManager = static::getContainer()->get(UserManager::class);
        $user = $userManager->findUserByEmail('test@mediaarea.net');

        // A country and a language which are at the top of the lists, as preferred choices
        $user->setCountry('FR');
        $user->setLanguage('en_US');
        $userManager->updateUser($user);

        $crawler = $client->request('GET', '/profile/edit');
        $form = $crawler->selectButton('Update')->form();

        // The page changes the user in memory for its display, the one of the database is restored
        static::getContainer()->get('doctrine')->getManager()->refresh($user);
        $user->setCountry(null);
        $user->setLanguage(null);
        $userManager->updateUser($user);

        $this->assertEquals('FR', $form->get('fos_user_profile_form[country]')->getValue());
        $this->assertEquals('en_US', $form->get('fos_user_profile_form[language]')->getValue());
    }
}
