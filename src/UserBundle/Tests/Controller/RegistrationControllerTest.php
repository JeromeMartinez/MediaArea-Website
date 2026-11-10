<?php

namespace UserBundle\Tests\Controller;

use MediaConchOnlineBundle\Mailer\UserMailer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UriSigner;
use UserBundle\Entity\User;
use UserBundle\Lib\TokenGenerator;
use UserBundle\Lib\UserManager;

class RegistrationControllerTest extends WebTestCase
{
    const EMAIL = 'usertest@mediaarea.net';
    const CONFIRMATION_EMAIL = 'confirmtest@mediaarea.net';

    protected function tearDown(): void
    {
        // The users made by the tests are removed
        $userManager = static::getContainer()->get(UserManager::class);
        foreach ([self::EMAIL, self::CONFIRMATION_EMAIL] as $email) {
            if (null !== $user = $userManager->findUserByEmail($email)) {
                $userManager->deleteUser($user);
            }
        }

        parent::tearDown();
    }

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

        $email = self::EMAIL;
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
        // The email address does not have to be confirmed
        $this->assertTrue($user->isEnabled());
        $this->assertCount(0, $this->getMailerMessages());
        $userManager->deleteUser($user);
    }

    public function testRegisterFormWithUsername()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register/');
        $form = $crawler->selectButton('Register')->form();

        $email = self::EMAIL;
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

    /**
     * User whose email address is not confirmed, with the link of the confirmation email sent to him.
     *
     * @return array The user and the link
     */
    private function createUnconfirmedUser(): array
    {
        $userManager = static::getContainer()->get(UserManager::class);
        $user = $userManager->createUser();
        $user->setUsername('confirmtest');
        $user->setEmail(self::CONFIRMATION_EMAIL);
        $user->setPlainPassword(static::getContainer()->get(TokenGenerator::class)->generateToken());
        $user->setEnabled(false);
        $user->addRole('ROLE_BASIC');
        $userManager->updateUser($user);

        static::getContainer()->get(UserMailer::class)->sendConfirmationEmailMessage($user);

        $messages = $this->getMailerMessages();
        $this->assertCount(1, $messages);
        $this->assertSame(self::CONFIRMATION_EMAIL, $messages[0]->getTo()[0]->getAddress());
        $this->assertSame('Welcome confirmtest!', $messages[0]->getSubject());
        $this->assertSame(1, preg_match('#http://localhost/register/confirm/\S+#', $messages[0]->getTextBody(), $matches));

        return [$user, $matches[0]];
    }

    private function isEnabled(User $user): bool
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->clear();

        return $entityManager->getRepository(User::class)->find($user->getId())->isEnabled();
    }

    /**
     * Sign a link again after a modification of its parameters, as the website signs it.
     */
    private function sign(string $url, array $parameters): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        unset($query['signature']);
        $signer = new UriSigner(static::getContainer()->getParameter('kernel.secret'), 'signature');

        return $signer->sign('http://localhost'.$parts['path'].'?'.http_build_query(array_replace($query, $parameters)));
    }

    /**
     * The link of the confirmation email enables the account and logs in the user, once.
     */
    public function testConfirmation()
    {
        $client = static::createClient();
        [$user, $url] = $this->createUnconfirmedUser();

        // The link is the one of the user, signed, with an end of validity in 7 days
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('/register/confirm/'.$user->getId(), parse_url($url, PHP_URL_PATH));
        $this->assertEqualsCanonicalizing(['expires', 'signature', 'token'], array_keys($query));
        $this->assertEqualsWithDelta(time() + 604800, (int) $query['expires'], 60);

        $client->request('GET', $url);
        $this->assertTrue($client->getResponse()->isRedirect('/register/confirmed'));
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Congrats confirmtest, your account is now activated.', $crawler->filter('body')->text());
        $this->assertTrue($this->isEnabled($user));

        // Used again, in another browser, the link does not log in
        $client->restart();
        $client->request('GET', $url);
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $client->request('GET', '/profile/');
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testConfirmationWithWrongLink()
    {
        $client = static::createClient();
        [$user, $url] = $this->createUnconfirmedUser();
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $invalid = 'The link to verify your email is invalid.';
        $wrongUrls = [
            // Link of another user, or of nobody
            [str_replace('/confirm/'.$user->getId().'?', '/confirm/1?', $url), $invalid],
            [str_replace('/confirm/'.$user->getId().'?', '/confirm/999999999?', $url), $invalid],
            // Modified end of validity, with the signature of the link or without signature
            [str_replace('expires='.$query['expires'], 'expires='.($query['expires'] + 3600), $url), $invalid],
            [str_replace('&signature='.rawurlencode($query['signature']), '', $url), $invalid],
            // Signed link which is not the one of the address of the user
            [$this->sign($url, ['token' => 'wrong']), 'The link to verify your email appears to be for a different account or email.'],
        ];
        foreach ($wrongUrls as [$wrongUrl, $message]) {
            $this->assertNotSame($url, $wrongUrl);
            $client->request('GET', $wrongUrl);
            $this->assertTrue($client->getResponse()->isRedirect('/login'), $wrongUrl);
            $crawler = $client->followRedirect();
            $this->assertStringContainsString($message, $crawler->filter('.alert-danger')->text());
        }

        $this->assertFalse($this->isEnabled($user));
    }

    public function testConfirmationWithExpiredLink()
    {
        $client = static::createClient();
        [$user, $url] = $this->createUnconfirmedUser();

        $client->request('GET', $this->sign($url, ['expires' => time() - 60]));

        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('The link to verify your email has expired.', $crawler->filter('.alert-danger')->text());
        $this->assertFalse($this->isEnabled($user));
    }
}
