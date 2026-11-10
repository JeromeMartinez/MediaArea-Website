<?php

namespace PaymentBundle\Event;

use PaymentBundle\Entity\Payment;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The payment provider confirmed the payment of an order.
 */
class PaymentCapturedEvent extends Event
{
    private $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
