<?php

namespace PaymentBundle\Lib;

use PaymentBundle\Entity\Payment;

/**
 * What to do after a step of a payment.
 */
class PaymentResult
{
    private $payment;
    private $redirectUrl;

    public function __construct(Payment $payment, ?string $redirectUrl = null)
    {
        $this->payment = $payment;
        $this->redirectUrl = $redirectUrl;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * URL of the payment provider the user has to visit to continue the payment.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function isSuccess(): bool
    {
        return $this->payment->isCaptured();
    }

    public function isCanceled(): bool
    {
        return Payment::STATE_CANCELED === $this->payment->getState();
    }

    /**
     * Code of the error given by the payment provider.
     */
    public function getReasonCode(): ?string
    {
        return $this->payment->getReasonCode();
    }
}
