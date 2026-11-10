<?php

namespace PaymentBundle\Controller;

use PaymentBundle\Simulation\PaypalSimulator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages which replace the ones of the payment providers when the payments are simulated.
 */
#[Route('/payment/simulation')]
class SimulationController extends AbstractController
{
    private $simulator;
    private $paymentSimulation;

    public function __construct(PaypalSimulator $simulator, bool $paymentSimulation)
    {
        $this->simulator = $simulator;
        $this->paymentSimulation = $paymentSimulation;
    }

    /**
     * The "buyer" chooses what happens to the payment, then is sent back to the website as Paypal does.
     */
    #[Route('/paypal', name: 'payment_simulation_paypal', methods: ['GET', 'POST'])]
    public function paypalAction(Request $request)
    {
        // The page does not exist when the payments are real
        $token = (string) $request->query->get('token');
        $checkout = $this->paymentSimulation ? $this->simulator->getCheckout($token) : null;
        if (null === $checkout) {
            throw $this->createNotFoundException();
        }

        $outcome = $request->request->get('outcome');
        if ($request->isMethod('POST') &&
            null === $checkout['transactionId'] &&
            in_array($outcome, PaypalSimulator::OUTCOMES, true)) {
            return $this->redirect($this->simulator->choose($token, $outcome));
        }

        return $this->render('@Payment/Simulation/paypal.html.twig', [
            'noAds' => true,
            'checkout' => $checkout,
        ]);
    }
}
