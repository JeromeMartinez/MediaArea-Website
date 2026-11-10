<?php

namespace PaymentBundle\Lib;

use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Entity\User;
use PaymentBundle\Entity\Invoice;
use PaymentBundle\Entity\Payment;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateInvoice
{
    protected $tokenStorage;
    protected $em;
    protected $requestStack;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->em = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Create the invoice of a payment.
     */
    public function create(Payment $payment)
    {
        // Check if user is connected
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if (!$user instanceof User) {
            $user = null;
        }

        // Get country
        $clientIp = $this->requestStack->getCurrentRequest()->getClientIp();
        $ipToCountry = new IpToCountry($clientIp);
        $country = $ipToCountry->getCountryIsoCode('FR');

        // Calculate VAT
        $vat = new VatCalculator();
        $vat->setGross($payment->getAmount())
            ->setCountry('custom' == $payment->getOrder()->getType() ? 'XX' : $country)
            ->calculateNet();

        // Create invoice
        $invoice = new Invoice();
        $invoice->setUser($user)
            ->setPayment($payment)
            ->setAmount($vat->getNet())
            ->setVat($vat->getVatAmount())
            ->setCurrency($payment->getCurrency())
            ->setIpAddress($clientIp)
            ->setCountry($country)
            ->setDate($payment->getUpdatedAt());

        $this->em->persist($invoice);
        $this->em->flush();
    }
}
