<?php

namespace PaymentBundle\Tests\Method;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use PaymentBundle\Entity\Order;
use PaymentBundle\Entity\Payment;
use PaymentBundle\Method\PaypalExpressCheckoutMethod;
use PaymentBundle\Payum\HttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The gateway of Payum with the answers Paypal gives to the requests of its NVP API.
 */
class PaypalExpressCheckoutMethodTest extends TestCase
{
    /**
     * Requests sent to Paypal: URL and fields.
     */
    private $requests = [];

    /**
     * @param array[] $responses Fields of the successive answers of Paypal
     */
    private function createMethod(array $responses, ?Request $request = null): PaypalExpressCheckoutMethod
    {
        $this->requests = [];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            parse_str($options['body'], $fields);
            $this->requests[] = ['method' => $method, 'url' => $url, 'fields' => $fields];

            return new MockResponse(http_build_query(array_shift($responses) ?? ['ACK' => 'Failure']));
        });

        $requestStack = new RequestStack();
        $requestStack->push($request ?? Request::create('/orders/1/payment/create'));

        return new PaypalExpressCheckoutMethod(new HttpClient($client), $requestStack, 'user', 'password', 'sign', true);
    }

    private function createDetails(PaypalExpressCheckoutMethod $method): ArrayObject
    {
        $payment = new Payment(new Order('10.00000'), $method->getName(), '10.00000', 'EUR');

        return new ArrayObject($method->createDetails($payment, 'https://example.org/orders/1/payment/create'));
    }

    public function testRefusedCredentials()
    {
        $method = $this->createMethod([
            ['ACK' => 'Failure', 'L_ERRORCODE0' => '10002', 'L_SHORTMESSAGE0' => 'Security error'],
        ]);
        $details = $this->createDetails($method);

        $method->getGateway()->execute(new Capture($details));
        $method->getGateway()->execute($status = new GetHumanStatus($details));

        $this->assertTrue($status->isFailed());
        $this->assertSame('10002', $method->getReasonCode($details->getArrayCopy()));

        $this->assertCount(1, $this->requests);
        $this->assertSame('POST', $this->requests[0]['method']);
        $this->assertSame('https://api-3t.sandbox.paypal.com/nvp', $this->requests[0]['url']);
        $fields = $this->requests[0]['fields'];
        $this->assertSame('SetExpressCheckout', $fields['METHOD']);
        $this->assertSame('user', $fields['USER']);
        $this->assertSame('password', $fields['PWD']);
        $this->assertSame('sign', $fields['SIGNATURE']);
        $this->assertEquals(10, $fields['PAYMENTREQUEST_0_AMT']);
        $this->assertSame('EUR', $fields['PAYMENTREQUEST_0_CURRENCYCODE']);
        $this->assertSame('Sale', $fields['PAYMENTREQUEST_0_PAYMENTACTION']);
        $this->assertSame('https://example.org/orders/1/payment/create', $fields['RETURNURL']);
        $this->assertSame('https://example.org/orders/1/payment/create?cancelled=1', $fields['CANCELURL']);
    }

    public function testPayment()
    {
        // The user is sent to Paypal
        $method = $this->createMethod([
            ['ACK' => 'Success', 'TOKEN' => 'EC-1'],
            ['ACK' => 'Success', 'TOKEN' => 'EC-1', 'CHECKOUTSTATUS' => 'PaymentActionNotInitiated'],
        ]);
        $details = $this->createDetails($method);

        try {
            $method->getGateway()->execute(new Capture($details));
            $this->fail('The user is not redirected to Paypal.');
        } catch (HttpRedirect $reply) {
            $url = parse_url($reply->getUrl());
            parse_str($url['query'], $query);
            $this->assertSame('www.sandbox.paypal.com', $url['host']);
            $this->assertSame('/cgi-bin/webscr', $url['path']);
            $this->assertEquals(['cmd' => '_express-checkout', 'token' => 'EC-1', 'useraction' => 'commit'], $query);
        }

        $this->assertSame(['SetExpressCheckout', 'GetExpressCheckoutDetails'], array_column(array_column($this->requests, 'fields'), 'METHOD'));

        // The user is back: the stored details are used again, the payment is made
        $method = $this->createMethod([
            ['ACK' => 'Success', 'TOKEN' => 'EC-1', 'CHECKOUTSTATUS' => 'PaymentActionNotInitiated', 'PAYERID' => 'PAYER1', 'PAYMENTREQUEST_0_AMT' => '10.00'],
            ['ACK' => 'Success', 'TOKEN' => 'EC-1', 'PAYMENTINFO_0_TRANSACTIONID' => 'TX-1', 'PAYMENTINFO_0_PAYMENTSTATUS' => 'Completed', 'PAYMENTINFO_0_AMT' => '10.00'],
            ['ACK' => 'Success', 'TOKEN' => 'EC-1', 'CHECKOUTSTATUS' => 'PaymentActionCompleted', 'PAYERID' => 'PAYER1', 'PAYMENTREQUEST_0_AMT' => '10.00'],
        ], Request::create('/orders/1/payment/create?token=EC-1&PayerID=PAYER1'));
        $details = new ArrayObject($details->getArrayCopy());

        $method->getGateway()->execute(new Capture($details));
        $method->getGateway()->execute($status = new GetHumanStatus($details));

        $this->assertTrue($status->isCaptured());
        $this->assertSame('TX-1', $method->getReference($details->getArrayCopy()));

        $methods = array_column(array_column($this->requests, 'fields'), 'METHOD');
        $this->assertSame(['GetExpressCheckoutDetails', 'DoExpressCheckoutPayment', 'GetExpressCheckoutDetails'], $methods);
        $payment = $this->requests[1]['fields'];
        $this->assertSame('EC-1', $payment['TOKEN']);
        $this->assertSame('PAYER1', $payment['PAYERID']);
        $this->assertEquals(10, $payment['PAYMENTREQUEST_0_AMT']);
        $this->assertSame('EUR', $payment['PAYMENTREQUEST_0_CURRENCYCODE']);
        $this->assertSame('Sale', $payment['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    public function testCanceledByTheUser()
    {
        $method = $this->createMethod([], Request::create('/orders/1/payment/create?cancelled=1&token=EC-1'));
        $details = $this->createDetails($method);
        $details['TOKEN'] = 'EC-1';

        $method->getGateway()->execute(new Capture($details));
        $method->getGateway()->execute($status = new GetHumanStatus($details));

        $this->assertTrue($status->isCanceled());
        $this->assertCount(0, $this->requests);
    }
}
