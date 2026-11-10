<?php

namespace PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use PaymentBundle\Entity\Order;
use PaymentBundle\Lib\IpToCurrency;
use PaymentBundle\Lib\PaymentManager;

#[Route('/orders')]
class OrdersController extends AbstractController
{
    private $payments;
    private $em;

    public function __construct(PaymentManager $payments, EntityManagerInterface $em)
    {
        $this->payments = $payments;
        $this->em = $em;
    }

    #[Route('/{id}/payment/create')]
    public function paymentCreateAction(Order $order)
    {
        return $this->paymentCreate(
            $order,
            $this->generateUrl('payment_orders_paymentcomplete', ['id' => $order->getId()]),
            $this->generateUrl('mi_donate')
        );
    }

    #[Route('/{id}/payment/create/individual')]
    public function paymentCreateIndividualAction(Order $order)
    {
        return $this->paymentCreate(
            $order,
            $this->generateUrl('payment_orders_paymentcompleteindividual', ['id' => $order->getId()]),
            $this->generateUrl('supportUs_individual')
        );
    }

    #[Route('/{id}/payment/create/corporate')]
    public function paymentCreateCorporateAction(Order $order)
    {
        return $this->paymentCreate(
            $order,
            $this->generateUrl('payment_orders_paymentcompletecorporate', ['id' => $order->getId()]),
            $this->generateUrl('supportUs_corporate')
        );
    }

    #[Route('/{id}/payment/create/custom')]
    public function paymentCreateCustomAction(Order $order, Request $request)
    {
        return $this->paymentCreate(
            $order,
            $this->generateUrl(
                'payment_orders_paymentcompletecustom',
                ['id' => $order->getId(), 'routeParams' => $request->query->all('routeParams')]
            ),
            $this->generateUrl('supportUs_custom', $request->query->all('routeParams'))
        );
    }

    /**
     * Start the payment of an order, or continue it when the payment provider sends the user back.
     */
    private function paymentCreate(Order $order, $paymentCompleteUrl, $paymentErrorReturnUrl)
    {
        $payment = $order->getPayment();
        if (null === $payment) {
            throw $this->createNotFoundException('The order has no payment.');
        }

        $result = $this->payments->process($payment);

        if ($result->isSuccess()) {
            return $this->redirect($paymentCompleteUrl);
        }

        if (null !== $result->getRedirectUrl()) {
            return $this->redirect($result->getRedirectUrl());
        }

        // The user gave up at the payment provider
        if ($result->isCanceled()) {
            return $this->redirect($paymentErrorReturnUrl);
        }

        // Payment error
        $message = 'We are sorry but there is an error processing your payment';
        if ($result->getReasonCode()) {
            $message .= ' ('.htmlspecialchars($result->getReasonCode()).')';
        }

        $message .= '.<br>';
        $message .= '<a href="'.$this->generateUrl('ma_contact').'">Contact us</a> if the problem persists.';

        $this->addFlash('danger', $message);

        return $this->redirect($paymentErrorReturnUrl);
    }

    #[Route('/{id}/payment/complete')]
    public function paymentCompleteAction(Request $request, Order $order)
    {
        return $this->paymentComplete($request, $order, $this->generateUrl('mi_donate'));
    }

    #[Route('/{id}/payment/complete/individual')]
    public function paymentCompleteIndividualAction(Request $request, Order $order)
    {
        return $this->paymentComplete($request, $order, $this->generateUrl('supportUs_individual'));
    }

    #[Route('/{id}/payment/complete/corporate')]
    public function paymentCompleteCorporateAction(Request $request, Order $order)
    {
        return $this->paymentComplete($request, $order, $this->generateUrl('supportUs_corporate'));
    }

    #[Route('/{id}/payment/complete/custom')]
    public function paymentCompleteCustomAction(Request $request, Order $order)
    {
        return $this->paymentComplete(
            $request,
            $order,
            $this->generateUrl('supportUs_custom', $request->query->all('routeParams'))
        );
    }

    private function paymentComplete(Request $request, Order $order, $paymentErrorUrl)
    {
        // Redirect if user have already completed his payment
        if (Order::STATUS_COMPLETED === $order->getStatus()) {
            $this->addFlash('danger', 'Order already completed');

            return $this->redirect($paymentErrorUrl);
        }

        // Redirect if payment is not complete
        $payment = $order->getPayment();
        if (null === $payment || !$payment->isCaptured()) {
            $this->addFlash('danger', 'The requested payment is not complete');

            return $this->redirect($paymentErrorUrl);
        }

        // Set order completed
        $order->setStatus(Order::STATUS_COMPLETED);
        $this->em->persist($order);
        $this->em->flush();

        // Add flash message
        $ipToCurrency = new IpToCurrency($request->getClientIp());
        $message = sprintf(
            'Thank you, your payment of %s has been successfully processed',
            'custom' == $order->getType() ?
                (int) $order->getAmount().' '.$payment->getCurrency() :
                $ipToCurrency->amountWithCurrency((int) $order->getAmount())
        );

        $this->addFlash('success', $message);

        // Redirect connected users to profile page
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('fos_user_profile_show');
        }

        // Redirect anonymous users
        return $this->redirectToRoute('mi_home', ['Donated' => true]);
    }
}
