<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Lib\Recaptcha\RecaptchaVerifier;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

class ContactControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/Contact');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('Contact us', $crawler->filter('h1')->text());
        $this->assertEquals('MediaArea', trim($crawler->filter('#main-navbar ul.nav li.active a')->text()));
    }

    /**
     * Client for which the answer to the captcha, not checked with Google, is always the right one.
     *
     * @return KernelBrowser
     */
    private function createClientWithoutCaptcha()
    {
        $client = static::createClient();
        // Keep the services between the display of the form and its submission
        $client->disableReboot();

        $verifier = $this->createStub(RecaptchaVerifier::class);
        $verifier->method('verify')->willReturn(true);
        static::getContainer()->set(RecaptchaVerifier::class, $verifier);

        return $client;
    }

    public function testSendMessageWithoutCompany()
    {
        $client = $this->createClientWithoutCaptcha();

        $crawler = $client->request('GET', '/Contact');

        $form = $crawler->selectButton('Send message')->form();
        $form->setValues([
            'contact[name]' => 'Name',
            'contact[email]' => 'test@mediaarea.net',
            'contact[subject]' => 'Subject',
            'contact[message]' => 'Message',
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/Contact'));

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage();

        $this->assertInstanceOf(Email::class, $message);
        $this->assertEquals('Subject', $message->getSubject());
        $this->assertEquals('test@mediaarea.net', $message->getFrom()[0]->getAddress());
        $this->assertEquals('Name', $message->getFrom()[0]->getName());
        $this->assertEquals('info@mediaarea.net', $message->getTo()[0]->getAddress());
        $this->assertEquals("Source: web\n\nMessage", $message->getTextBody());
    }

    public function testSendMessageWithCompany()
    {
        $client = $this->createClientWithoutCaptcha();

        $crawler = $client->request('GET', '/Contact');

        $form = $crawler->selectButton('Send message')->form();
        $form->setValues([
            'contact[name]' => 'Name',
            'contact[email]' => 'test@mediaarea.net',
            'contact[subject]' => 'Subject',
            'contact[message]' => 'Message',
            'contact[company]' => 'My company',
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/Contact'));

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage();

        $this->assertInstanceOf(Email::class, $message);
        $this->assertEquals('Subject', $message->getSubject());
        $this->assertEquals('test@mediaarea.net', $message->getFrom()[0]->getAddress());
        $this->assertEquals('Name', $message->getFrom()[0]->getName());
        $this->assertEquals('info@mediaarea.net', $message->getTo()[0]->getAddress());
        $this->assertEquals("Source: web\nCompany: My company\n\nMessage", $message->getTextBody());
    }

    public function testSendMessageWithSponsor()
    {
        $client = $this->createClientWithoutCaptcha();

        $crawler = $client->request('GET', '/Contact?sponsor=Bronze+Sponsor&amount=3000+%E2%82%AC');

        $form = $crawler->selectButton('Send message')->form();
        $values = $form->getValues();
        $this->assertEquals('Request quote for Bronze Sponsor (3000 €)', $values['contact[subject]']);
        $form->setValues([
            'contact[name]' => 'Name',
            'contact[email]' => 'test@mediaarea.net',
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/Contact'));

        $this->assertEmailCount(1);

        $message = $this->getMailerMessage();

        $this->assertInstanceOf(Email::class, $message);
        $this->assertEquals('Request quote for Bronze Sponsor (3000 €)', $message->getSubject());
        $this->assertEquals('test@mediaarea.net', $message->getFrom()[0]->getAddress());
        $this->assertEquals('Name', $message->getFrom()[0]->getName());
        $this->assertEquals('info@mediaarea.net', $message->getTo()[0]->getAddress());
        $this->assertEquals("Source: web\n\nI'm interested in becoming a MediaArea sponsor.\n\nPlease send me a quote of 3000 € to become a Bronze Sponsor.", $message->getTextBody());
    }
}
