<?php

namespace PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'invoice')]
#[ORM\Entity]
class Invoice
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \UserBundle\Entity\User::class)]
    private $user;

    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\OneToOne(targetEntity: Payment::class)]
    private $payment;

    /**
     * Name of the payment method, e.g. paypal_express_checkout.
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $paymentMethod;

    /**
     * Payment instruction of JMSPaymentCoreBundle, for the invoices made when this bundle was used:
     * its tables are kept in the database as archives.
     */
    #[ORM\Column(name: 'payment_instruction_id', type: 'integer', nullable: true)]
    private $legacyPaymentInstructionId;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 5)]
    private $amount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 5)]
    private $vat;

    #[ORM\Column(type: 'string', length: 3)]
    protected $currency;

    #[ORM\Column(type: 'string', length: 45)]
    protected $ipAddress;

    #[ORM\Column(type: 'string', length: 2)]
    protected $country;

    #[ORM\Column(type: 'datetime')]
    private $date;

    public function __construct()
    {
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount.
     *
     * @param string $amount
     *
     * @return Invoice
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set vat.
     *
     * @param string $vat
     *
     * @return Invoice
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat.
     *
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set currency.
     *
     * @param string $currency
     *
     * @return Invoice
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set ipAddress.
     *
     * @param string $ipAddress
     *
     * @return Invoice
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress.
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return Invoice
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Invoice
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user.
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Invoice
     */
    public function setUser(?\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the payment and its method.
     *
     * @param Payment|null $payment
     *
     * @return Invoice
     */
    public function setPayment(?Payment $payment = null)
    {
        $this->payment = $payment;
        if (null !== $payment) {
            $this->paymentMethod = $payment->getMethod();
        }

        return $this;
    }

    /**
     * Get payment.
     *
     * @return Payment|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Get the identifier of the archived payment instruction of an old invoice.
     *
     * @return int|null
     */
    public function getLegacyPaymentInstructionId()
    {
        return $this->legacyPaymentInstructionId;
    }

    /**
     * Get payment system name.
     *
     * @return string|null
     */
    public function getPaymentSystemName()
    {
        return $this->paymentMethod;
    }

    /**
     * Get short payment system name.
     *
     * @return string
     */
    public function getShortPaymentSystemName()
    {
        switch ($this->paymentMethod) {
            case 'paypal_express_checkout':
                return 'Paypal';
                break;
            case 'stripe_credit_card':
                return 'Stripe';
                break;
            default:
                return (string) $this->paymentMethod;
        }
    }
}
