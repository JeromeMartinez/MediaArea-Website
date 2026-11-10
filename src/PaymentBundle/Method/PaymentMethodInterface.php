<?php

namespace PaymentBundle\Method;

use Payum\Core\GatewayInterface;
use PaymentBundle\Entity\Payment;

/**
 * A way to pay an order: a Payum gateway and what is specific to it in the data exchanged with the payment provider.
 */
interface PaymentMethodInterface
{
    /**
     * Name of the method, stored with the payments and the invoices.
     */
    public function getName(): string;

    /**
     * Label of the method in the payment form.
     */
    public function getLabel(): string;

    public function getGateway(): GatewayInterface;

    /**
     * Data given to the gateway for a new payment.
     *
     * @param string $returnUrl URL the payment provider sends the user to, it continues the payment
     */
    public function createDetails(Payment $payment, string $returnUrl): array;

    /**
     * Identifier of the transaction at the payment provider, when the payment is done.
     */
    public function getReference(array $details): ?string;

    /**
     * Code of the error given by the payment provider, when the payment failed.
     */
    public function getReasonCode(array $details): ?string;
}
