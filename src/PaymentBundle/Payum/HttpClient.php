<?php

namespace PaymentBundle\Payum;

use GuzzleHttp\Psr7\Response;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;

/**
 * Sends the requests of the Payum gateways with the HTTP client of Symfony.
 */
class HttpClient implements HttpClientInterface
{
    private $client;

    public function __construct(SymfonyHttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        $response = $this->client->request($request->getMethod(), (string) $request->getUri(), [
            'headers' => $request->getHeaders(),
            'body' => (string) $request->getBody(),
        ]);

        return new Response($response->getStatusCode(), $response->getHeaders(false), $response->getContent(false));
    }
}
