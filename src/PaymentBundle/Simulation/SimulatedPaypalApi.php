<?php

namespace PaymentBundle\Simulation;

use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * API of Paypal for the gateway of Payum when the payments are simulated: the requests are answered by the simulator
 * instead of being sent to Paypal, and the buyer is sent to a page of the website instead of the one of Paypal.
 *
 * The gateway itself is the one used for the real payments.
 */
class SimulatedPaypalApi extends Api
{
    private $simulator;
    private $router;

    public function __construct(PaypalSimulator $simulator, UrlGeneratorInterface $router)
    {
        // The API of Paypal requires credentials and a HTTP client, they are not used
        $client = new class implements HttpClientInterface {
            public function send(RequestInterface $request)
            {
                throw new \LogicException('Nothing is sent to Paypal when the payments are simulated.');
            }
        };

        parent::__construct(
            ['username' => 'simulation', 'password' => 'simulation', 'signature' => 'simulation', 'sandbox' => true],
            $client,
            new GuzzleMessageFactory()
        );

        $this->simulator = $simulator;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeTokenUrl($token, array $query = [])
    {
        return $this->router->generate(
            'payment_simulation_paypal',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doRequest(array $fields)
    {
        // The simulator gets what Paypal gets: the fields which are set, as strings
        parse_str(http_build_query($fields), $fields);

        return $this->simulator->request($fields);
    }
}
