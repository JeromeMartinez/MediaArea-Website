<?php

namespace PaymentBundle\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use PaymentBundle\Entity\Order;
use PaymentBundle\Entity\Payment;
use PaymentBundle\Event\PaymentCapturedEvent;
use PaymentBundle\Method\MethodRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Creates the payments of the orders and runs them with the gateway of their payment method.
 */
class PaymentManager
{
    private $em;
    private $methods;
    private $cipher;
    private $dispatcher;

    public function __construct(
        EntityManagerInterface $em,
        MethodRegistry $methods,
        DetailsCipher $cipher,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->methods = $methods;
        $this->cipher = $cipher;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Create the payment of an order.
     *
     * @param string $returnUrl URL which calls process(): the payment provider sends the user back to it
     */
    public function create(Order $order, string $method, string $currency, string $returnUrl): Payment
    {
        $payment = new Payment($order, $method, $order->getAmount(), $currency);
        $payment->setDetails($this->cipher->encrypt($this->methods->get($method)->createDetails($payment, $returnUrl)));

        $this->em->persist($payment);
        $this->em->flush();

        return $payment;
    }

    /**
     * Start or continue a payment: the result is final, or has the URL of the payment provider to visit.
     */
    public function process(Payment $payment): PaymentResult
    {
        if (!in_array($payment->getState(), [Payment::STATE_NEW, Payment::STATE_PENDING], true)) {
            return new PaymentResult($payment);
        }

        $method = $this->methods->get($payment->getMethod());
        $gateway = $method->getGateway();
        $details = new ArrayObject($this->cipher->decrypt($payment->getDetails()));

        try {
            $gateway->execute(new Capture($details));
        } catch (HttpRedirect $reply) {
            $payment->setState(Payment::STATE_PENDING);
            $this->save($payment, $details);

            return new PaymentResult($payment, $reply->getUrl());
        }

        $gateway->execute($status = new GetHumanStatus($details));
        if ($status->isCaptured()) {
            $payment->setState(Payment::STATE_CAPTURED);
        } elseif ($status->isCanceled()) {
            $payment->setState(Payment::STATE_CANCELED);
        } elseif ($status->isPending() || $status->isNew()) {
            $payment->setState(Payment::STATE_PENDING);
        } else {
            $payment->setState(Payment::STATE_FAILED);
        }

        $data = $details->getArrayCopy();
        $payment->setReference($method->getReference($data));
        $payment->setReasonCode($payment->isCaptured() ? null : $method->getReasonCode($data));
        $this->save($payment, $details);

        if ($payment->isCaptured()) {
            $this->dispatcher->dispatch(new PaymentCapturedEvent($payment));
        }

        return new PaymentResult($payment);
    }

    private function save(Payment $payment, ArrayObject $details): void
    {
        $payment->setDetails($this->cipher->encrypt($details->getArrayCopy()));

        $this->em->persist($payment);
        $this->em->flush();
    }
}
