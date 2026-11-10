<?php

namespace PaymentBundle\Tests\Entity;

use PaymentBundle\Entity\Order;
use PaymentBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderTest extends KernelTestCase
{
    /**
     * The payment of an order is first a proxy, it is loaded when it is read.
     */
    public function testPaymentOfAnOrderIsLoaded()
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $order = new Order(10);
        $payment = new Payment($order, 'paypal_express_checkout', $order->getAmount(), 'EUR');
        $payment->setState(Payment::STATE_CAPTURED);
        $entityManager->persist($order);
        $entityManager->persist($payment);
        $entityManager->flush();
        $orderId = $order->getId();
        $entityManager->clear();

        $order = $entityManager->getRepository(Order::class)->find($orderId);
        $payment = $order->getPayment();
        $state = $payment->getState();
        $currency = $payment->getCurrency();
        $method = $payment->getMethod();

        $entityManager->remove($payment);
        $entityManager->remove($order);
        $entityManager->flush();

        $this->assertSame(Payment::STATE_CAPTURED, $state);
        $this->assertSame('EUR', $currency);
        $this->assertSame('paypal_express_checkout', $method);
    }
}
