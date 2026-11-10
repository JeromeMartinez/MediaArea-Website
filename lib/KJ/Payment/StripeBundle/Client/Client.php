<?php

namespace KJ\Payment\StripeBundle\Client;

use Stripe\Balance;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Token;

class Client
{
    // Information from https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
    protected $zero_array_currency = array(
        "BIF", "DJF", "JPY", "KRW", "PYG", "VND", "XAF", "XPF",
        "CLP", "GNF", "KMF", "MGA", "RWF", "VUV", "XOF");

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var null|string
     */
    protected $apiVersion;

    /**
     * @param string $apiKey
     * @param string $apiVersion
     */
    public function __construct($apiKey, $apiVersion = null)
    {
        $this->apiKey = $apiKey;
        $this->apiVersion = $apiVersion;

        Stripe::setApiKey($this->apiKey);

        if ($this->apiVersion) {
            Stripe::setApiVersion($this->apiVersion);
        }
    }

    /**
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @param array $cardDetails
     * @param bool $capture
     * @return Response
     */
    public function chargeCard($amount, $currency, $description, array $cardDetails, $capture = true)
    {
        return $this->sendChargeRequest('create', array(
            'capture' => $capture,
            'amount'  => $this->convertAmountToStripeFormat($amount, $currency),
            'currency' => $currency,
            'description' => $description,
            'card' => $cardDetails
        ));
    }

    /**
     * Capture a charge
     *
     * @param $chargeId
     * @return Response
     */
    public function capture($chargeId)
    {
        $response = $this->sendChargeRequest('retrieve', $chargeId);

        if ($response->isSuccess()) {
            $response->getResponse()->capture();
        }

        return $response;
    }

    /**
     * @param $method
     * @param $param
     * @return Response
     */
    protected function sendChargeRequest($method, $param)
    {
        try {
            $response = Charge::$method($param);
        } catch (\Exception $e) {
            return new Response(null, $e);
        }
        return new Response($response);
    }

    /**
     * @param array $cardDetails
     * @return Response
     */
    public function createChargeToken(array $cardDetails)
    {
        return $this->sendTokenRequest('create', array(
            'card' => $cardDetails
        ));
    }

    /**
     * @param $method
     * @param $param
     * @return Response
     */
    protected function sendTokenRequest($method, $param)
    {
        try {
            $response = Token::$method($param);
        } catch (\Exception $e) {
            return new Response(null, $e);
        }
        return new Response($response);
    }

    /**
     * @param string|array $card
     * @param string $planId
     * @param array $optionalParams
     * @return Response
     */
    public function createCustomerRequest($card, $planId, array $optionalParams = array())
    {
        $allowedParams = array(
            'account_balance',
            'coupon',
            'description',
            'email',
            'metadata',
            'quantity',
            'trial_end'
        );

        $optionalParams = array_intersect_key($optionalParams, array_flip($allowedParams));

        return $this->sendCustomerRequest(
            'create',
            array_merge(
                $optionalParams,
                array(
                    'card' => $card,
                    'plan' => $planId
                )
            )
        );
    }

    /**
     * @param $customerId
     * @return Response
     */
    public function retrieveCustomer($customerId)
    {
        return $this->sendCustomerRequest('retrieve', $customerId);
    }

    /**
     * @param $method
     * @param $param
     * @return Response
     */
    protected function sendCustomerRequest($method, $param)
    {
        try {
            $response = Customer::$method($param);
        } catch (\Exception $e) {
            return new Response(null, $e);
        }
        return new Response($response);
    }


    /**
     * Full or Part refund
     *
     * @param string $chargeId
     * @param float $amount
     * @param bool $refundApplicationFee
     * @return Response
     */
    public function refund($chargeId, $amount, $currency, $refundApplicationFee = false)
    {
        $response = $this->sendChargeRequest('retrieve', $chargeId);

        if ($response->isSuccess()) {

            try {
                $refundResponse = Refund::create(array(
                    'charge' => $chargeId,
                    'amount' => $this->convertAmountToStripeFormat($amount, $currency),
                    'refund_application_fee' => $refundApplicationFee,
                ));

            } catch (\Exception $e) {
                return new Response(null, $e);
            }

            return new Response($refundResponse);
        }

        return $response;
    }

    /**
     * @param array $params
     * @return Response
     */
    public function createPlan(array $params)
    {
        $allowedParams = array('id', 'amount', 'currency', 'interval', 'interval_count', 'name', 'trial_period_days');

        $params = array_intersect_key($params, array_flip($allowedParams));

        return $this->sendPlanRequest('create', $params);
    }

    /**
     * @param string $planId
     * @return Response
     */
    public function retrievePlan($planId)
    {
        return $this->sendPlanRequest('retrieve', $planId);
    }

    /**
     * @param $method
     * @param $param
     * @return Response
     */
    protected function sendPlanRequest($method, $param)
    {
        try {
            $response = Plan::$method($param);
        } catch (\Exception $e) {
            return new Response(null, $e);
        }
        return new Response($response);
    }

    /**
     * Test api credentials by attempting a balance request
     *
     * @return bool
     */
    public function testCredentials()
    {
        try {
            Balance::retrieve();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function isZeroCurrency($currency) {
        return (in_array(strtoupper($currency), $this->zero_array_currency));
    }

    /**
     * Convert transaction amount to stripe amount format
     *
     * @param float $amount
     * @return integer
     */
    public function convertAmountToStripeFormat($amount, $currency)
    {
        if ($this->isZeroCurrency($currency)) {
            return $amount;
        } else {
            return $amount * 100;
        }
    }

    /**
     * Convert transaction amount reported from Stripe API
     *
     * @param $amount
     * @return float
     */
    public function convertAmountFromStripeFormat($amount, $currency)
    {
        if ($this->isZeroCurrency($currency)) {
            return $amount;
        } else {
            return $amount / 100;
        }
    }
}
