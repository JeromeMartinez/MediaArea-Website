<?php

namespace PaymentBundle\EventListener;

use UserBundle\Entity\User;
use PaymentBundle\Event\PaymentCapturedEvent;
use PaymentBundle\Lib\CreateInvoice;
use SupportUsBundle\Lib\Individual;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UserBundle\Lib\DonorManipulator;

class OrdersListener
{
    protected $createInvoice;
    protected $tokenStorage;
    protected $donorManipulator;

    public function __construct(
        CreateInvoice $createInvoice,
        TokenStorageInterface $tokenStorage,
        DonorManipulator $donorManipulator
    ) {
        $this->createInvoice = $createInvoice;
        $this->tokenStorage = $tokenStorage;
        $this->donorManipulator = $donorManipulator;
    }

    /**
     * Create the invoice of a payment and give the donation to the user.
     */
    public function onPaymentCaptured(PaymentCapturedEvent $event)
    {
        $payment = $event->getPayment();
        $order = $payment->getOrder();

        // Create invoice
        $this->createInvoice->create($payment);

        // Add donation to user if user is connected
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if ($user instanceof User && 'individual' == $order->getType()) {
            $individual = new Individual();
            $votes = $individual->amountToVotes($payment->getAmount(), $payment->getCurrency());
            $date = $individual->amountToMembership($payment->getAmount(), $payment->getCurrency());

            $this->donorManipulator->addAmountVotesAndMembershipDateToUser(
                $user,
                $payment->getAmount(),
                $votes,
                $date
            );
        }
    }
}
