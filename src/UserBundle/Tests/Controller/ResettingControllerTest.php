<?php

namespace UserBundle\Tests\Controller;

use AppBundle\Lib\Recaptcha\RecaptchaVerifier;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use UserBundle\Entity\ResetPasswordRequest;
use UserBundle\Entity\User;
use UserBundle\Lib\UserManager;

class ResettingControllerTest extends UserAbstractControllerTestCase
{
    const EMAIL = 'test@mediaarea.net';
    const TEMPORARY_EMAIL = 'resettest@mediaarea.net';

    /**
     * Password of the user before the test: hash and salt.
     */
    private $password;

    protected function tearDown(): void
    {
        $entityManager = $this->getEntityManager();
        $user = $this->getUser($entityManager);

        // The user has the password he had, and no reset request
        $entityManager->createQuery('DELETE FROM '.ResetPasswordRequest::class.' r WHERE r.user = :user')
            ->setParameter('user', $user)
            ->execute();
        if (null !== $this->password) {
            $user->setPassword($this->password['hash']);
            $user->setSalt($this->password['salt']);
            $entityManager->flush();
        }

        $userManager = static::getContainer()->get(UserManager::class);
        if (null !== $temporaryUser = $userManager->findUserByEmail(self::TEMPORARY_EMAIL)) {
            $userManager->deleteUser($temporaryUser);
        }

        parent::tearDown();
    }

    /**
     * Entity manager without the entities of the previous requests: they are read from the database.
     */
    private function getEntityManager()
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->clear();

        return $entityManager;
    }

    private function getUser($entityManager): User
    {
        return $entityManager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
    }

    private function getRequests(): array
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->getRepository(ResetPasswordRequest::class)->findBy(['user' => $this->getUser($entityManager)]);
    }

    /**
     * Client for which the answer to the captcha, not checked with Google, is always the right one.
     */
    private function createClientWithoutCaptcha(): KernelBrowser
    {
        $client = static::createClient();
        // Keep the services between the display of the form and its submission
        $client->disableReboot();

        $verifier = $this->createStub(RecaptchaVerifier::class);
        $verifier->method('verify')->willReturn(true);
        static::getContainer()->set(RecaptchaVerifier::class, $verifier);

        return $client;
    }

    /**
     * Ask for the reset of the password of a user with the form.
     *
     * @return array Emails sent
     */
    private function requestReset(KernelBrowser $client, string $username): array
    {
        $crawler = $client->request('GET', '/resetting/request');
        // Depending on the version of Symfony, the emails of the previous requests of the client are kept or not
        $sent = count($this->getMailerMessages());
        $form = $crawler->selectButton('Reset password')->form();
        $form['username'] = $username;
        $client->submit($form);
        $messages = array_slice($this->getMailerMessages(), $sent);

        // The page is the same whether an email is sent or not
        $this->assertTrue($client->getResponse()->isRedirect('/resetting/check-email?username='.$username));
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('once within 2 hours', $crawler->filter('body')->text());

        return $messages;
    }

    public function testReset()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->selectButton('Reset password')->form();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEmpty(trim($form->get('username')->getValue()));
    }

    public function testResetWithEmailParam()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resetting/request?email=test@mediaarea.net');
        $form = $crawler->selectButton('Reset password')->form();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('test@mediaarea.net', trim($form->get('username')->getValue()));
    }

    public function testResetConnected()
    {
        $client = $this->createRegularUserClient();

        $client->request('GET', '/resetting/request');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/profile/'));
    }

    /**
     * The whole reset, as the user does it: request, link of the email, new password.
     */
    public function testPasswordIsReset()
    {
        $client = $this->createClientWithoutCaptcha();
        $user = $this->getUser($this->getEntityManager());
        $this->password = ['hash' => $user->getPassword(), 'salt' => $user->getSalt()];

        $messages = $this->requestReset($client, self::EMAIL);

        // The email has the link of the reset
        $this->assertCount(1, $messages);
        $this->assertSame(self::EMAIL, $messages[0]->getTo()[0]->getAddress());
        $this->assertSame('info@mediaarea.net', $messages[0]->getFrom()[0]->getAddress());
        $this->assertSame(1, preg_match('#http://localhost(/resetting/reset/(\w{40}))\s#', $messages[0]->getTextBody(), $matches));
        [, $path, $token] = $matches;

        // The request is stored for a day, without the token of the link
        $requests = $this->getRequests();
        $this->assertCount(1, $requests);
        $this->assertFalse($requests[0]->isExpired());
        $this->assertEqualsWithDelta(time() + 86400, $requests[0]->getExpiresAt()->getTimestamp(), 60);
        $this->assertStringNotContainsString(substr($token, 20), $requests[0]->getHashedToken());

        // Another email can not be asked for now
        $this->assertCount(0, $this->requestReset($client, self::EMAIL));
        $this->assertCount(1, $this->getRequests());

        // The link gives the form of the new password
        $crawler = $client->request('GET', $path);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Change password')->form();
        $form['fos_user_resetting_form[plainPassword][first]'] = 'A new password, 1 2 3';
        $form['fos_user_resetting_form[plainPassword][second]'] = 'Another one';
        $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSame($this->password['hash'], $this->getUser($this->getEntityManager())->getPassword());

        $form = $client->getCrawler()->selectButton('Change password')->form();
        $form['fos_user_resetting_form[plainPassword][first]'] = 'A new password, 1 2 3';
        $form['fos_user_resetting_form[plainPassword][second]'] = 'A new password, 1 2 3';
        $client->submit($form);

        // The password is changed and the user is logged in
        $this->assertTrue($client->getResponse()->isRedirect('/profile/'));
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('The password has been reset successfully.', $crawler->filter('.alert-success')->text());
        $this->assertEquals('Email: '.self::EMAIL, trim($crawler->filter('.fos_user_user_show .panel-body p')->text()));

        $user = $this->getUser($this->getEntityManager());
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertNotSame($this->password['hash'], $user->getPassword());
        $this->assertTrue($hasher->isPasswordValid($user, 'A new password, 1 2 3'));

        // The link works once
        $this->assertCount(0, $this->getRequests());
        $client->request('GET', $path);
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
    }

    public function testRequestForUnknownUser()
    {
        $client = $this->createClientWithoutCaptcha();

        $this->assertCount(0, $this->requestReset($client, 'nobody@mediaarea.net'));
    }

    public function testExpiredLink()
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        $user = $this->getUser($entityManager);
        $token = static::getContainer()->get(ResetPasswordHelperInterface::class)->generateResetToken($user)->getToken();

        $client->request('GET', '/resetting/reset/'.$token);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // The user has to make a new request
        $this->getEntityManager()->createQuery('UPDATE '.ResetPasswordRequest::class.' r SET r.expiresAt = :date')
            ->setParameter('date', new \DateTimeImmutable('-1 minute'))
            ->execute();
        $client->request('GET', '/resetting/reset/'.$token);
        $this->assertTrue($client->getResponse()->isRedirect('/resetting/request'));
    }

    public function testUnknownLink()
    {
        $client = static::createClient();
        $token = static::getContainer()->get(ResetPasswordHelperInterface::class)
            ->generateResetToken($this->getUser($this->getEntityManager()))
            ->getToken();

        // A token which is not the one of a request, with the selector of a request or not
        foreach ([str_repeat('a', 40), substr($token, 0, 20).str_repeat('a', 20), 'short'] as $wrongToken) {
            $client->request('GET', '/resetting/reset/'.$wrongToken);
            $this->assertTrue($client->getResponse()->isRedirect('/login'), $wrongToken);
        }
    }

    /**
     * A user who has asked to reset his password can be removed.
     */
    public function testRequestsAreRemovedWithTheUser()
    {
        $userManager = static::getContainer()->get(UserManager::class);
        $user = $userManager->createUser();
        $user->setUsername('resettest');
        $user->setEmail(self::TEMPORARY_EMAIL);
        $user->setPlainPassword('A password, 1 2 3');
        $user->setEnabled(true);
        $userManager->updateUser($user);
        $userId = $user->getId();
        static::getContainer()->get(ResetPasswordHelperInterface::class)->generateResetToken($user);

        $connection = static::getContainer()->get('doctrine')->getConnection();
        $count = 'SELECT COUNT(*) FROM reset_password_request WHERE user_id = ?';
        $this->assertEquals(1, $connection->fetchOne($count, [$userId]));

        $userManager->deleteUser($user);

        $this->assertEquals(0, $connection->fetchOne($count, [$userId]));
    }
}
