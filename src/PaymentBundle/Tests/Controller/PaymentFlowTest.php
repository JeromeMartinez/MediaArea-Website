<?php

namespace PaymentBundle\Tests\Controller;

use AppBundle\Lib\Recaptcha\RecaptchaVerifier;
use PaymentBundle\Entity\Invoice;
use PaymentBundle\Entity\Order;
use PaymentBundle\Entity\Payment;
use SupportUsBundle\Lib\Individual;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use UserBundle\Entity\User;
use UserBundle\Tests\Controller\UserAbstractControllerTestCase;

/**
 * Payments made on the website, from the form to the completed order, with the simulation of Paypal: the tests do
 * what a user does in his browser, the page which replaces the one of Paypal included.
 */
class PaymentFlowTest extends UserAbstractControllerTestCase
{
    const USER_EMAIL = 'test@mediaarea.net';

    /**
     * Identifier of the order made by the test, removed with its payment and its invoice when the test ends.
     */
    private $orderId;

    /**
     * What the payments change for the user, as it was before the test.
     */
    private $userState;

    protected function tearDown(): void
    {
        $entityManager = $this->getEntityManager();

        if (null !== $this->orderId && null !== $order = $entityManager->getRepository(Order::class)->find($this->orderId)) {
            if (null !== $payment = $order->getPayment()) {
                foreach ($entityManager->getRepository(Invoice::class)->findBy(['payment' => $payment]) as $invoice) {
                    $entityManager->remove($invoice);
                }
                $entityManager->remove($payment);
            }
            $entityManager->remove($order);
        }

        if (null !== $this->userState) {
            $user = $this->getUser($entityManager);
            $user->setTotalDonated($this->userState['totalDonated']);
            $user->setVote($this->userState['vote']);
            $user->setEndDate($this->userState['endDate']);
        }

        $entityManager->flush();
        $this->orderId = null;
        $this->userState = null;

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
        return $entityManager->getRepository(User::class)->findOneBy(['email' => self::USER_EMAIL]);
    }

    /**
     * Client of the user whose donations are restored when the test ends.
     */
    private function createUserClient(): KernelBrowser
    {
        $client = $this->createRegularUserClient();

        $user = $this->getUser($this->getEntityManager());
        $this->userState = [
            'totalDonated' => $user->getTotalDonated(),
            'vote' => $user->getVote(),
            'endDate' => $user->getEndDate(),
        ];

        return $client;
    }

    /**
     * Submit the payment form of a page, up to the page which replaces the one of Paypal.
     *
     * @param string|null $amount Amount to pay, the one of the form when null
     *
     * @return Crawler The page which replaces the one of Paypal
     */
    private function startPayment(KernelBrowser $client, string $url, ?string $amount = null): Crawler
    {
        $crawler = $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name="ma_choose_payment_method"]')->form();
        $this->assertSame('paypal_express_checkout', $form['ma_choose_payment_method[method]']->getValue());
        if (null !== $amount) {
            $form['ma_choose_payment_method[amount]']->setValue($amount);
        }
        $client->submit($form);

        // The order is made, its payment starts
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $location = $client->getResponse()->headers->get('Location');
        $this->assertMatchesRegularExpression('#^/orders/\d+/payment/create#', $location);
        $this->orderId = (int) explode('/', $location)[2];
        $client->followRedirect();

        // The user is sent to the payment provider: the simulated one
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringStartsWith(
            'http://localhost/payment/simulation/paypal?token=EC-SIMULATED',
            $client->getResponse()->headers->get('Location')
        );
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('Simulated payment with Paypal', $crawler->filter('h1')->text());

        return $crawler;
    }

    /**
     * Choose what happens to the payment on the page which replaces the one of Paypal, up to the last page.
     *
     * @param string $outcome  Value of the button of the page
     * @param string ...$pages Start of the URL of the successive pages the user is sent to after the one of the order
     *
     * @return Crawler The last page
     */
    private function finishPayment(KernelBrowser $client, Crawler $crawler, string $outcome, string ...$pages): Crawler
    {
        $client->submit($crawler->filter('form.payment-simulation button[value="'.$outcome.'"]')->form());

        // The user is sent back to the order, as Paypal does
        array_unshift($pages, 'http://localhost/orders/'.$this->orderId.'/payment/create');
        foreach ($pages as $page) {
            $this->assertEquals(302, $client->getResponse()->getStatusCode());
            $this->assertStringStartsWith($page, $client->getResponse()->headers->get('Location'));
            $crawler = $client->followRedirect();
        }
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        return $crawler;
    }

    /**
     * Order made by the test, with its payment, as they are in the database.
     */
    private function getOrder(): Order
    {
        return $this->getEntityManager()->getRepository(Order::class)->find($this->orderId);
    }

    private function getInvoices(Order $order): array
    {
        return static::getContainer()->get('doctrine')->getManager()->getRepository(Invoice::class)->findBy(['payment' => $order->getPayment()]);
    }

    public function testIndividualPaymentOfUser()
    {
        $client = $this->createUserClient();

        $crawler = $this->startPayment($client, '/SupportUs/Individual', '30');
        $this->assertEquals('Payment of 30.00 EUR to MediaArea.', $crawler->filter('.payment-simulation-amount')->text());
        $simulationUrl = $crawler->getUri();

        $crawler = $this->finishPayment(
            $client,
            $crawler,
            'pay',
            '/orders/'.$this->orderId.'/payment/complete/individual',
            '/profile/'
        );
        $this->assertEquals(
            'Thank you, your payment of 30 € has been successfully processed',
            trim($crawler->filter('.alert-success')->text(), "× \n")
        );

        // The order is completed, with its payment and its invoice
        $order = $this->getOrder();
        $payment = $order->getPayment();
        $this->assertSame(Order::STATUS_COMPLETED, $order->getStatus());
        $this->assertSame('individual', $order->getType());
        $this->assertSame(Payment::STATE_CAPTURED, $payment->getState());
        $this->assertSame('paypal_express_checkout', $payment->getMethod());
        $this->assertEquals(30, $payment->getAmount());
        $this->assertSame('EUR', $payment->getCurrency());
        $this->assertStringStartsWith('SIMULATED-', $payment->getReference());
        $this->assertNull($payment->getReasonCode());
        // The data exchanged with the payment provider is encrypted
        $this->assertStringNotContainsString('SIMULATED', $payment->getDetails());

        $invoices = $this->getInvoices($order);
        $this->assertCount(1, $invoices);
        $this->assertSame(self::USER_EMAIL, $invoices[0]->getUser()->getEmail());
        $this->assertEquals(25, $invoices[0]->getAmount());
        $this->assertEquals(5, $invoices[0]->getVat());
        $this->assertSame('EUR', $invoices[0]->getCurrency());
        $this->assertSame('FR', $invoices[0]->getCountry());
        $this->assertSame('paypal_express_checkout', $invoices[0]->getPaymentSystemName());

        // The user gets what his donation gives
        $individual = new Individual();
        $user = $invoices[0]->getUser();
        $this->assertEquals($this->userState['totalDonated'] + 30, $user->getTotalDonated());
        $this->assertEquals($this->userState['vote'] + $individual->amountToVotes(30, 'EUR'), $user->getVote());
        $this->assertGreaterThan($this->userState['vote'], $user->getVote());
        $this->assertEquals($individual->amountToMembership(30, 'EUR'), $user->getEndDate());

        // The payment is made once: the page of Paypal and the one of the order do not make it again
        $crawler = $client->request('GET', $simulationUrl);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(0, $crawler->filter('form.payment-simulation'));
        $this->assertStringContainsString('is already made', $crawler->filter('.payment-simulation-amount')->text());

        $client->request('GET', '/orders/'.$this->orderId.'/payment/create/individual');
        $this->assertTrue($client->getResponse()->isRedirect('/orders/'.$this->orderId.'/payment/complete/individual'));
        $client->followRedirect();
        $this->assertTrue($client->getResponse()->isRedirect('/SupportUs/Individual'));
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Order already completed', $crawler->filter('.alert-danger')->text());

        $order = $this->getOrder();
        $invoices = $this->getInvoices($order);
        $this->assertCount(1, $invoices);
        $this->assertEquals($this->userState['totalDonated'] + 30, $invoices[0]->getUser()->getTotalDonated());
    }

    public function testCanceledPayment()
    {
        $client = $this->createUserClient();

        $crawler = $this->startPayment($client, '/SupportUs/Individual', '30');
        $crawler = $this->finishPayment($client, $crawler, 'cancel', '/SupportUs/Individual');

        // The user gets the form again, without error
        $this->assertCount(0, $crawler->filter('.alert-danger'));
        $this->assertCount(1, $crawler->filter('form[name="ma_choose_payment_method"]'));

        $order = $this->getOrder();
        $this->assertSame(Order::STATUS_NEW, $order->getStatus());
        $this->assertSame(Payment::STATE_CANCELED, $order->getPayment()->getState());
        $this->assertNull($order->getPayment()->getReference());
        $this->assertCount(0, $this->getInvoices($order));
        $this->assertEquals($this->userState['totalDonated'], $this->getUser($this->getEntityManager())->getTotalDonated());
    }

    public function testDeclinedPayment()
    {
        $client = $this->createUserClient();

        $crawler = $this->startPayment($client, '/SupportUs/Individual', '30');
        $crawler = $this->finishPayment($client, $crawler, 'decline', '/SupportUs/Individual');

        $this->assertStringContainsString(
            'We are sorry but there is an error processing your payment (10486).',
            $crawler->filter('.alert-danger')->text()
        );

        $order = $this->getOrder();
        $this->assertSame(Order::STATUS_NEW, $order->getStatus());
        $this->assertSame(Payment::STATE_FAILED, $order->getPayment()->getState());
        $this->assertSame('10486', $order->getPayment()->getReasonCode());
        $this->assertNull($order->getPayment()->getReference());
        $this->assertCount(0, $this->getInvoices($order));
        $this->assertEquals($this->userState['totalDonated'], $this->getUser($this->getEntityManager())->getTotalDonated());
    }

    public function testHeldPayment()
    {
        $client = $this->createUserClient();

        $crawler = $this->startPayment($client, '/SupportUs/Individual', '30');
        $crawler = $this->finishPayment($client, $crawler, 'pending', '/SupportUs/Individual');

        $this->assertStringContainsString(
            'We are sorry but there is an error processing your payment (Pending).',
            $crawler->filter('.alert-danger')->text()
        );

        // The payment is known but nothing is given for it
        $order = $this->getOrder();
        $this->assertSame(Order::STATUS_NEW, $order->getStatus());
        $this->assertSame(Payment::STATE_PENDING, $order->getPayment()->getState());
        $this->assertSame('Pending', $order->getPayment()->getReasonCode());
        $this->assertStringStartsWith('SIMULATED-', $order->getPayment()->getReference());
        $this->assertCount(0, $this->getInvoices($order));
        $this->assertEquals($this->userState['totalDonated'], $this->getUser($this->getEntityManager())->getTotalDonated());
    }

    public function testCustomPaymentOfAnonymous()
    {
        $client = static::createClient();

        // The amount and the currency are the ones of the URL
        $crawler = $this->startPayment($client, '/SupportUs/Custom?amount=25&currency=USD');
        $this->assertEquals('Payment of 25.00 USD to MediaArea.', $crawler->filter('.payment-simulation-amount')->text());

        $this->finishPayment(
            $client,
            $crawler,
            'pay',
            '/orders/'.$this->orderId.'/payment/complete/custom?',
            '/en/MediaInfo?Donated=1',
            '/en/MediaInfo'
        );
        // The user is known as a donor by his browser
        $this->assertSame('Y', $client->getCookieJar()->get('Donated')->getValue());

        $order = $this->getOrder();
        $this->assertSame(Order::STATUS_COMPLETED, $order->getStatus());
        $this->assertSame('custom', $order->getType());
        $this->assertSame(Payment::STATE_CAPTURED, $order->getPayment()->getState());
        $this->assertSame('USD', $order->getPayment()->getCurrency());

        // An invoice without user and without VAT
        $invoices = $this->getInvoices($order);
        $this->assertCount(1, $invoices);
        $this->assertNull($invoices[0]->getUser());
        $this->assertEquals(25, $invoices[0]->getAmount());
        $this->assertEquals(0, $invoices[0]->getVat());
        $this->assertSame('USD', $invoices[0]->getCurrency());
    }

    public function testCanceledCustomPayment()
    {
        $client = static::createClient();

        $crawler = $this->startPayment($client, '/SupportUs/Custom?amount=25&currency=USD');
        $crawler = $this->finishPayment($client, $crawler, 'cancel', '/SupportUs/Custom?amount=25&currency=USD');

        // The user gets the form of his payment again
        $this->assertCount(0, $crawler->filter('.alert-danger'));
        $this->assertSame('25', $crawler->filter('form[name="ma_choose_payment_method"]')->form()['ma_choose_payment_method[amount]']->getValue());
        $this->assertSame(Payment::STATE_CANCELED, $this->getOrder()->getPayment()->getState());
    }

    public function testDonationWithCaptcha()
    {
        $client = static::createClient();
        // Keep the services between the requests: the answer to the captcha, not checked with Google, is the right one
        $client->disableReboot();
        $verifier = $this->createStub(RecaptchaVerifier::class);
        $verifier->method('verify')->willReturn(true);
        static::getContainer()->set(RecaptchaVerifier::class, $verifier);

        $crawler = $client->request('GET', '/en/MediaInfo/Donate');
        // The user sees that the payments are simulated
        $this->assertStringContainsString('Paypal (simulation)', $crawler->filter('form[name="ma_choose_payment_method"]')->text());

        $crawler = $this->startPayment($client, '/en/MediaInfo/Donate', '5');
        $this->assertEquals('Payment of 5.00 EUR to MediaArea.', $crawler->filter('.payment-simulation-amount')->text());

        $this->finishPayment(
            $client,
            $crawler,
            'pay',
            '/orders/'.$this->orderId.'/payment/complete',
            '/en/MediaInfo?Donated=1',
            '/en/MediaInfo'
        );
        $this->assertSame('Y', $client->getCookieJar()->get('Donated')->getValue());

        $order = $this->getOrder();
        $this->assertSame(Order::STATUS_COMPLETED, $order->getStatus());
        $this->assertNull($order->getType());
        $this->assertSame(Payment::STATE_CAPTURED, $order->getPayment()->getState());
        $this->assertEquals(5, $order->getPayment()->getAmount());
        $this->assertCount(1, $this->getInvoices($order));
    }

    public function testSimulationPageWithoutCheckout()
    {
        $client = static::createClient();

        $client->request('GET', '/payment/simulation/paypal');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/payment/simulation/paypal?token=EC-UNKNOWN');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('POST', '/payment/simulation/paypal?token=EC-UNKNOWN', ['outcome' => 'pay']);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
