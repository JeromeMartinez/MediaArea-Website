<?php

namespace PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'orders')]
#[ORM\Entity]
class Order
{
    const STATUS_NEW = 0;
    const STATUS_COMPLETED = 1;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\OneToOne(targetEntity: Payment::class, mappedBy: 'order')]
    private $payment;

    /**
     * Payment instruction of JMSPaymentCoreBundle, for the orders made when this bundle was used:
     * its tables are kept in the database as archives.
     */
    #[ORM\Column(name: 'payment_instruction_id', type: 'integer', nullable: true)]
    private $legacyPaymentInstructionId;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 5)]
    private $amount;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $type;

    #[ORM\Column(type: 'smallint', nullable: false, options: ['default' => 0])]
    protected $status = 0;

    public function __construct($amount)
    {
        $this->amount = $amount;
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
     * @return Order
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return Order
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Order
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set payment.
     *
     * @param Payment|null $payment
     *
     * @return Order
     */
    public function setPayment(?Payment $payment = null)
    {
        $this->payment = $payment;

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
     * Get the identifier of the archived payment instruction of an old order.
     *
     * @return int|null
     */
    public function getLegacyPaymentInstructionId()
    {
        return $this->legacyPaymentInstructionId;
    }
}
