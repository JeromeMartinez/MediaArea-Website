# Bundled third party code

The payment bundles used by the website are not maintained any more and can not be installed with current versions
of Symfony and Doctrine, their code is kept here and updated for them.

| Directory | Origin | License |
| --- | --- | --- |
| `JMS/Payment/CoreBundle` | [jms/payment-core-bundle](https://github.com/schmittjoh/JMSPaymentCoreBundle) 1.4.1 | Apache-2.0, see `LICENSE` in the directory |
| `JMS/Payment/PaypalBundle` | [jms/payment-paypal-bundle](https://github.com/schmittjoh/JMSPaymentPaypalBundle) 1.2.0 | Apache-2.0, see `LICENSE` in the directory |
| `KJ/Payment/StripeBundle` | [mediaarea/kj-payment-stripe-bundle](https://github.com/MediaArea/KJPaymentStripeBundle), `dependencies` branch | MIT |

Changes from the original code:

* configuration of the services in PHP instead of XML, configuration trees for the current Symfony Config component;
* `extended_payment_data` Doctrine type without the removed `object` type of Doctrine DBAL, the format in database is the same;
* event classes and dispatcher calls of the current EventDispatcher component;
* form types and validation constraints for the current Form and Validator components;
* Stripe client for the current `stripe/stripe-php`;
* tests, documentation, mcrypt encryption, the unused `GatewayPlugin` base class (it needs `symfony/browser-kit`, a testing
  component) and compatibility layers for old versions of Symfony removed.

The database schema and the calls to the payment providers are unchanged.
