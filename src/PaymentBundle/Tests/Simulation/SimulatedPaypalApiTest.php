<?php

namespace PaymentBundle\Tests\Simulation;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use PaymentBundle\Controller\SimulationController;
use PaymentBundle\Entity\Order;
use PaymentBundle\Entity\Payment;
use PaymentBundle\Method\PaypalExpressCheckoutMethod;
use PaymentBundle\Payum\HttpClient;
use PaymentBundle\Simulation\PaypalSimulator;
use PaymentBundle\Simulation\SimulatedPaypalApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The gateway of Payum with the simulated API of Paypal, as the website uses it when the payments are simulated.
 */
class SimulatedPaypalApiTest extends TestCase
{
    const ORDER_URL = 'https://example.org/orders/1/payment/create';

    /**
     * @var PaypalSimulator
     */
    private $simulator;

    /**
     * Number of requests sent to Paypal.
     */
    private $paypalRequests = 0;

    protected function setUp(): void
    {
        $this->simulator = new PaypalSimulator(new ArrayAdapter());
        $this->paypalRequests = 0;
    }

    /**
     * Payment method of the website, for a request of the user.
     */
    private function createMethod(bool $simulation, ?Request $request = null): PaypalExpressCheckoutMethod
    {
        $router = $this->createStub(UrlGeneratorInterface::class);
        $router->method('generate')->willReturnCallback(function (string $name, array $parameters) {
            return 'https://example.org/'.$name.'?'.http_build_query($parameters);
        });

        // Paypal refuses the credentials
        $client = new MockHttpClient(function () {
            ++$this->paypalRequests;

            return new MockResponse('ACK=Failure&L_ERRORCODE0=10002');
        });

        $requestStack = new RequestStack();
        $requestStack->push($request ?? Request::create('/orders/1/payment/create'));

        return new PaypalExpressCheckoutMethod(
            new HttpClient($client),
            $requestStack,
            'user',
            'password',
            'signature',
            true,
            new SimulatedPaypalApi($this->simulator, $router),
            $simulation
        );
    }

    /**
     * Run a payment up to the page which replaces the one of Paypal.
     *
     * @return array Details of the payment and token of the checkout
     */
    private function startPayment(): array
    {
        $method = $this->createMethod(true);
        $payment = new Payment(new Order('10.00000'), $method->getName(), '10.00000', 'EUR');
        $details = new ArrayObject($method->createDetails($payment, self::ORDER_URL));

        try {
            $method->getGateway()->execute(new Capture($details));
            $this->fail('The user is not sent to the page which replaces the one of Paypal.');
        } catch (HttpRedirect $reply) {
            $this->assertSame('https://example.org/payment_simulation_paypal?token='.$details['TOKEN'], $reply->getUrl());
        }

        $checkout = $this->simulator->getCheckout($details['TOKEN']);
        $this->assertSame('10.00', $checkout['amount']);
        $this->assertSame('EUR', $checkout['currency']);
        $this->assertSame(self::ORDER_URL, $checkout['returnUrl']);
        $this->assertSame(self::ORDER_URL.'?cancelled=1', $checkout['cancelUrl']);

        return [$details->getArrayCopy(), $details['TOKEN']];
    }

    /**
     * Continue a payment when the user is back on the website, at the URL given by the simulator.
     *
     * @return array Status of the payment, payment method and details of the payment
     */
    private function continuePayment(array $details, string $url): array
    {
        $method = $this->createMethod(true, Request::create($url));
        $details = new ArrayObject($details);

        $method->getGateway()->execute(new Capture($details));
        $method->getGateway()->execute($status = new GetHumanStatus($details));

        $this->assertSame(0, $this->paypalRequests);

        return [$status, $method, $details->getArrayCopy()];
    }

    public function testPaidCheckout()
    {
        [$details, $token] = $this->startPayment();
        $url = $this->simulator->choose($token, PaypalSimulator::OUTCOME_PAY);

        [$status, $method, $details] = $this->continuePayment($details, $url);

        $this->assertTrue($status->isCaptured());
        $this->assertStringStartsWith('SIMULATED-', $method->getReference($details));
        $this->assertSame($this->simulator->getCheckout($token)['transactionId'], $method->getReference($details));
        $this->assertSame('form.label.paypal_express_checkout_simulation', $method->getLabel());
    }

    public function testCanceledCheckout()
    {
        [$details, $token] = $this->startPayment();
        $url = $this->simulator->choose($token, PaypalSimulator::OUTCOME_CANCEL);

        [$status] = $this->continuePayment($details, $url);

        $this->assertTrue($status->isCanceled());
    }

    public function testDeclinedPayment()
    {
        [$details, $token] = $this->startPayment();
        $url = $this->simulator->choose($token, PaypalSimulator::OUTCOME_DECLINE);

        [$status, $method, $details] = $this->continuePayment($details, $url);

        $this->assertTrue($status->isFailed());
        $this->assertSame('10486', $method->getReasonCode($details));
        $this->assertNull($method->getReference($details));
    }

    public function testHeldPayment()
    {
        [$details, $token] = $this->startPayment();
        $url = $this->simulator->choose($token, PaypalSimulator::OUTCOME_PENDING);

        [$status, $method, $details] = $this->continuePayment($details, $url);

        $this->assertTrue($status->isPending());
        $this->assertSame('Pending', $method->getReasonCode($details));
        $this->assertStringStartsWith('SIMULATED-', $method->getReference($details));
    }

    /**
     * Paypal is called unless the simulation is asked.
     */
    public function testPaypalWithoutSimulation()
    {
        $method = $this->createMethod(false);
        $payment = new Payment(new Order('10.00000'), $method->getName(), '10.00000', 'EUR');
        $details = new ArrayObject($method->createDetails($payment, self::ORDER_URL));

        $method->getGateway()->execute(new Capture($details));

        $this->assertSame(1, $this->paypalRequests);
        $this->assertSame('10002', $method->getReasonCode($details->getArrayCopy()));
        $this->assertSame('form.label.paypal_express_checkout', $method->getLabel());
    }

    /**
     * The page which replaces the one of Paypal does not exist unless the simulation is asked.
     */
    public function testPageWithoutSimulation()
    {
        [, $token] = $this->startPayment();
        $controller = new SimulationController($this->simulator, false);

        $this->expectException(NotFoundHttpException::class);
        $controller->paypalAction(Request::create('/payment/simulation/paypal?token='.$token));
    }
}
