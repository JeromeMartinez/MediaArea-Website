<?php

namespace PaymentBundle\Tests\Simulation;

use PaymentBundle\Simulation\PaypalSimulator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Answers of the simulator to the requests of the NVP API of Paypal.
 */
class PaypalSimulatorTest extends TestCase
{
    const ORDER_URL = 'https://example.org/orders/1/payment/create';

    /**
     * @var PaypalSimulator
     */
    private $simulator;

    protected function setUp(): void
    {
        $this->simulator = new PaypalSimulator(new ArrayAdapter());
    }

    /**
     * Start a checkout and give its token.
     */
    private function setExpressCheckout(): string
    {
        $response = $this->simulator->request([
            'METHOD' => 'SetExpressCheckout',
            'VERSION' => '65.1',
            'PAYMENTREQUEST_0_AMT' => '10',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'RETURNURL' => self::ORDER_URL,
            'CANCELURL' => self::ORDER_URL.'?cancelled=1',
        ]);

        $this->assertSame('Success', $response['ACK']);
        $this->assertSame('65.1', $response['VERSION']);
        $this->assertStringStartsWith('EC-SIMULATED', $response['TOKEN']);

        return $response['TOKEN'];
    }

    private function doExpressCheckoutPayment(string $token, string $amount = '10.00'): array
    {
        return $this->simulator->request([
            'METHOD' => 'DoExpressCheckoutPayment',
            'TOKEN' => $token,
            'PAYERID' => PaypalSimulator::PAYER_ID,
            'PAYMENTREQUEST_0_AMT' => $amount,
        ]);
    }

    public function testCheckoutIsKept()
    {
        $token = $this->setExpressCheckout();

        $checkout = $this->simulator->getCheckout($token);
        $this->assertSame('10.00', $checkout['amount']);
        $this->assertSame('EUR', $checkout['currency']);
        $this->assertNull($checkout['transactionId']);

        $details = $this->simulator->request(['METHOD' => 'GetExpressCheckoutDetails', 'TOKEN' => $token]);
        $this->assertSame('Success', $details['ACK']);
        $this->assertSame('PaymentActionNotInitiated', $details['CHECKOUTSTATUS']);
        $this->assertSame('10.00', $details['PAYMENTREQUEST_0_AMT']);
        $this->assertSame('EUR', $details['PAYMENTREQUEST_0_CURRENCYCODE']);
        $this->assertArrayNotHasKey('PAYERID', $details);

        $this->assertNull($this->simulator->getCheckout('EC-UNKNOWN'));
    }

    public function testPaidCheckout()
    {
        $token = $this->setExpressCheckout();

        $url = $this->simulator->choose($token, PaypalSimulator::OUTCOME_PAY);
        $this->assertSame(self::ORDER_URL.'?token='.$token.'&PayerID='.PaypalSimulator::PAYER_ID, $url);

        $details = $this->simulator->request(['METHOD' => 'GetExpressCheckoutDetails', 'TOKEN' => $token]);
        $this->assertSame(PaypalSimulator::PAYER_ID, $details['PAYERID']);
        $this->assertSame('PaymentActionNotInitiated', $details['CHECKOUTSTATUS']);

        $payment = $this->doExpressCheckoutPayment($token);
        $this->assertSame('Success', $payment['ACK']);
        $this->assertSame('Completed', $payment['PAYMENTINFO_0_PAYMENTSTATUS']);
        $this->assertSame('10.00', $payment['PAYMENTINFO_0_AMT']);
        $this->assertSame('EUR', $payment['PAYMENTINFO_0_CURRENCYCODE']);
        $this->assertStringStartsWith('SIMULATED-', $payment['PAYMENTINFO_0_TRANSACTIONID']);

        $details = $this->simulator->request(['METHOD' => 'GetExpressCheckoutDetails', 'TOKEN' => $token]);
        $this->assertSame('PaymentActionCompleted', $details['CHECKOUTSTATUS']);
        $this->assertSame($payment['PAYMENTINFO_0_TRANSACTIONID'], $details['PAYMENTREQUEST_0_TRANSACTIONID']);

        $transaction = $this->simulator->request([
            'METHOD' => 'GetTransactionDetails',
            'TRANSACTIONID' => $payment['PAYMENTINFO_0_TRANSACTIONID'],
        ]);
        $this->assertSame('Success', $transaction['ACK']);
        $this->assertSame('Completed', $transaction['PAYMENTSTATUS']);
        $this->assertSame('10.00', $transaction['AMT']);

        // A checkout is paid once
        $this->assertSame('10415', $this->doExpressCheckoutPayment($token)['L_ERRORCODE0']);
        $this->expectException(\LogicException::class);
        $this->simulator->choose($token, PaypalSimulator::OUTCOME_PAY);
    }

    public function testCanceledCheckout()
    {
        $token = $this->setExpressCheckout();

        $url = $this->simulator->choose($token, PaypalSimulator::OUTCOME_CANCEL);
        $this->assertSame(self::ORDER_URL.'?cancelled=1&token='.$token, $url);

        // The buyer did not accept the payment
        $payment = $this->doExpressCheckoutPayment($token);
        $this->assertSame('Failure', $payment['ACK']);
        $this->assertSame('10406', $payment['L_ERRORCODE0']);
    }

    public function testDeclinedPayment()
    {
        $token = $this->setExpressCheckout();
        $this->simulator->choose($token, PaypalSimulator::OUTCOME_DECLINE);

        $payment = $this->doExpressCheckoutPayment($token);

        $this->assertSame('Failure', $payment['ACK']);
        $this->assertSame('10486', $payment['L_ERRORCODE0']);
        $this->assertNull($this->simulator->getCheckout($token)['transactionId']);
    }

    public function testHeldPayment()
    {
        $token = $this->setExpressCheckout();
        $this->simulator->choose($token, PaypalSimulator::OUTCOME_PENDING);

        $payment = $this->doExpressCheckoutPayment($token);

        $this->assertSame('Success', $payment['ACK']);
        $this->assertSame('Pending', $payment['PAYMENTINFO_0_PAYMENTSTATUS']);
        $this->assertSame('paymentreview', $payment['PAYMENTINFO_0_PENDINGREASON']);
        $transaction = $this->simulator->request([
            'METHOD' => 'GetTransactionDetails',
            'TRANSACTIONID' => $payment['PAYMENTINFO_0_TRANSACTIONID'],
        ]);
        $this->assertSame('Pending', $transaction['PAYMENTSTATUS']);
    }

    public function testRefusedRequests()
    {
        $urls = ['RETURNURL' => self::ORDER_URL, 'CANCELURL' => self::ORDER_URL];
        $refused = [
            ['81002', ['METHOD' => 'RefundTransaction']],
            ['10525', ['METHOD' => 'SetExpressCheckout', 'PAYMENTREQUEST_0_AMT' => '0'] + $urls],
            ['10605', ['METHOD' => 'SetExpressCheckout', 'PAYMENTREQUEST_0_AMT' => '1', 'PAYMENTREQUEST_0_CURRENCYCODE' => 'XXX'] + $urls],
            ['10471', ['METHOD' => 'SetExpressCheckout', 'PAYMENTREQUEST_0_AMT' => '1', 'CANCELURL' => self::ORDER_URL]],
            ['10472', ['METHOD' => 'SetExpressCheckout', 'PAYMENTREQUEST_0_AMT' => '1', 'RETURNURL' => self::ORDER_URL]],
            ['10410', ['METHOD' => 'GetExpressCheckoutDetails', 'TOKEN' => 'EC-UNKNOWN']],
            ['10410', ['METHOD' => 'DoExpressCheckoutPayment', 'TOKEN' => 'EC-UNKNOWN']],
            ['10004', ['METHOD' => 'GetTransactionDetails', 'TRANSACTIONID' => 'UNKNOWN']],
        ];
        foreach ($refused as [$code, $fields]) {
            $response = $this->simulator->request($fields);
            $this->assertSame('Failure', $response['ACK'], $code);
            $this->assertSame($code, $response['L_ERRORCODE0']);
        }

        // The amount paid is the one of the checkout
        $token = $this->setExpressCheckout();
        $this->simulator->choose($token, PaypalSimulator::OUTCOME_PAY);
        $this->assertSame('10413', $this->doExpressCheckoutPayment($token, '1.00')['L_ERRORCODE0']);

        $this->expectException(\InvalidArgumentException::class);
        $this->simulator->choose($token, 'unknown');
    }
}
