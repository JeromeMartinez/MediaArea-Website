<?php

namespace VoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'votes')]
#[ORM\Index(name: 'user_feature_idx', columns: ['user_id', 'feature_id'])]
#[ORM\Entity(repositoryClass: \VoteBundle\Repository\VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \UserBundle\Entity\User::class)]
    protected $user;

    #[ORM\JoinColumn(name: 'feature_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \VoteBundle\Entity\Feature::class)]
    protected $feature;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $points = 0;

    #[ORM\Column(type: 'datetime')]
    private $date;

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
     * Set points.
     *
     * @param int $points
     *
     * @return Vote
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Vote
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
     * @return Vote
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
     * Set feature.
     *
     * @param \VoteBundle\Entity\Feature $feature
     *
     * @return Vote
     */
    public function setFeature(?\VoteBundle\Entity\Feature $feature = null)
    {
        $this->feature = $feature;

        return $this;
    }

    /**
     * Get feature.
     *
     * @return \VoteBundle\Entity\Feature
     */
    public function getFeature()
    {
        return $this->feature;
    }
}
