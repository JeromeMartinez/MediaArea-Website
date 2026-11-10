<?php

namespace PaymentBundle\Tests\Entity;

use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use PaymentBundle\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderTest extends KernelTestCase
{
    /**
     * The payment instruction of an order is first a proxy, it is loaded when a payment is created.
     */
    public function testPaymentInstructionOfAnOrderIsLoaded()
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $instruction = new PaymentInstruction(10, 'EUR', 'paypal_express_checkout');
        $instruction->setState(PaymentInstructionInterface::STATE_VALID);
        $order = new Order(10);
        $order->setPaymentInstruction($instruction);
        $entityManager->persist($instruction);
        $entityManager->persist($order);
        $entityManager->flush();
        $orderId = $order->getId();
        $entityManager->clear();

        $order = $entityManager->getRepository(Order::class)->find($orderId);
        $instruction = $order->getPaymentInstruction();
        $state = $instruction->getState();
        $currency = $instruction->getCurrency();
        $paymentSystemName = $instruction->getPaymentSystemName();

        $entityManager->remove($order);
        $entityManager->remove($instruction);
        $entityManager->flush();

        $this->assertSame(PaymentInstructionInterface::STATE_VALID, $state);
        $this->assertSame('EUR', $currency);
        $this->assertSame('paypal_express_checkout', $paymentSystemName);
    }
}
