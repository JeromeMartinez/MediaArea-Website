<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[ORM\Table(name: 'users')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: 'usernameCanonical',
    errorPath: 'username',
    message: 'fos_user.username.already_used',
    groups: ['UserRegistration', 'UserProfile'],
)]
#[UniqueEntity(
    fields: 'emailCanonical',
    errorPath: 'email',
    message: 'fos_user.email.already_used',
    groups: ['UserRegistration', 'UserProfile'],
)]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface, EquatableInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';

    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(type: 'string', length: 180)]
    #[Assert\Length(
        min: 2,
        max: 180,
        minMessage: 'fos_user.username.short',
        maxMessage: 'fos_user.username.long',
        groups: ['UserRegistration', 'UserProfile'],
    )]
    protected $username;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected $usernameCanonical;

    #[ORM\Column(type: 'string', length: 180)]
    #[Assert\NotBlank(message: 'fos_user.email.blank', groups: ['UserRegistration', 'UserProfile'])]
    #[Assert\Length(
        min: 2,
        max: 180,
        minMessage: 'fos_user.email.short',
        maxMessage: 'fos_user.email.long',
        groups: ['UserRegistration', 'UserProfile'],
    )]
    #[Assert\Email(message: 'fos_user.email.invalid', groups: ['UserRegistration', 'UserProfile'])]
    protected $email;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected $emailCanonical;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = false;

    /**
     * The salt of the password, for the accounts which do not use a self salted hash.
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $salt;

    /**
     * Hashed password.
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $password;

    /**
     * Plain password, used for model validation: it is not persisted.
     */
    #[Assert\NotBlank(
        message: 'fos_user.password.blank',
        groups: ['UserRegistration', 'ResetPassword', 'ChangePassword'],
    )]
    #[Assert\Length(
        min: 6,
        max: 4096,
        minMessage: 'fos_user.password.short',
        groups: ['UserRegistration', 'UserProfile', 'ResetPassword', 'ChangePassword'],
    )]
    protected $plainPassword;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $lastLogin;

    /**
     * Stored as a serialized PHP array.
     */
    #[ORM\Column(type: 'array')]
    protected $roles = [];

    #[ORM\OneToMany(
        targetEntity: \MediaConchOnlineBundle\Entity\DisplayFile::class,
        mappedBy: 'user',
        cascade: ['persist', 'remove'],
    )]
    #[Assert\Valid]
    protected $display;

    #[ORM\OneToOne(targetEntity: UserQuotas::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    protected $quotas;

    #[ORM\OneToOne(targetEntity: UserQuotasDefault::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    protected $quotasDefault;

    #[ORM\OneToMany(targetEntity: Settings::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    protected $settings;

    #[ORM\OneToOne(targetEntity: GuestToken::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    protected $guestToken;

    #[ORM\OneToMany(
        targetEntity: ApiKey::class,
        mappedBy: 'user',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
    )]
    #[Assert\Valid]
    protected $apiKey;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Your name is too short (min 2).',
        maxMessage: 'Your name is too long (max 255).',
        groups: ['Registration', 'Profile'],
    )]
    protected $name;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $professional;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'The company name is too short (min 2).',
        maxMessage: 'The company name is too long (max 255).',
        groups: ['Registration', 'Profile'],
    )]
    protected $companyName;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 1])]
    protected $newsletter = true;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    protected $country;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $language;

    #[ORM\Column(type: 'integer', nullable: false, options: ['unsigned' => true, 'default' => 0])]
    protected $totalDonated = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['unsigned' => true, 'default' => 0])]
    protected $vote = 0;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected $displayName = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 1])]
    protected $realUserName = true;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $endDate;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function __construct()
    {
        $this->display = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->apiKey = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    /**
     * Only what is needed to refresh the user is stored in the session.
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'usernameCanonical' => $this->usernameCanonical,
            'email' => $this->email,
            'emailCanonical' => $this->emailCanonical,
            'enabled' => $this->enabled,
            'salt' => $this->salt,
            // The hash itself does not have to be in the session
            'password' => null === $this->password ? null : hash('crc32c', $this->password),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->usernameCanonical = $data['usernameCanonical'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->emailCanonical = $data['emailCanonical'] ?? null;
        $this->enabled = $data['enabled'] ?? false;
        $this->salt = $data['salt'] ?? null;
        $this->password = $data['password'] ?? null;
    }

    /**
     * Check that the user of the session is still the one of the database.
     *
     * The roles are not compared: they are refreshed at each request instead, see RolesRefreshSubscriber.
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self || $this->id !== $user->getId()) {
            return false;
        }

        $password = (string) $user->getPassword();
        if ($this->password !== $password && $this->password !== hash('crc32c', $password)) {
            return false;
        }

        return $this->salt === $user->getSalt()
            && $this->usernameCanonical === $user->getUsernameCanonical()
            && $this->enabled === $user->isEnabled();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get usernameCanonical.
     *
     * @return string
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * Set usernameCanonical.
     *
     * @param string $usernameCanonical
     *
     * @return User
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get emailCanonical.
     *
     * @return string
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Set emailCanonical.
     *
     * @param string $emailCanonical
     *
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    /**
     * Is the account enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * Set salt.
     *
     * @param string|null $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the hashed password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get plainPassword.
     *
     * @return string|null
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set plainPassword.
     *
     * @param string|null $password
     *
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Remove the sensitive data which must not be kept.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Get lastLogin.
     *
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set lastLogin.
     *
     * @param \DateTime|null $time
     *
     * @return User
     */
    public function setLastLogin(?\DateTime $time = null)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * Get roles, always with the default one.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles ?? [];
        $roles[] = static::ROLE_DEFAULT;

        return array_values(array_unique($roles));
    }

    /**
     * Set roles.
     *
     * @param string[] $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Add role.
     *
     * @param string $role
     *
     * @return User
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove role.
     *
     * @param string $role
     *
     * @return User
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Has role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * Is the user a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    /**
     * Set or unset the super admin role.
     *
     * @param bool $boolean
     *
     * @return User
     */
    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    /**
     * Gets triggered only on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set professional.
     *
     * @param bool $professional
     *
     * @return User
     */
    public function setProfessional($professional)
    {
        $this->professional = $professional;

        return $this;
    }

    /**
     * Get professional.
     *
     * @return bool
     */
    public function getProfessional()
    {
        return $this->professional;
    }

    /**
     * Set companyName.
     *
     * @param string $companyName
     *
     * @return User
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set newsletter.
     *
     * @param bool $newsletter
     *
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter.
     *
     * @return bool
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return User
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
     * Set language.
     *
     * @param string $language
     *
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set totalDonated.
     *
     * @param int $totalDonated
     *
     * @return User
     */
    public function setTotalDonated($totalDonated)
    {
        $this->totalDonated = $totalDonated;

        return $this;
    }

    /**
     * Get totalDonated.
     *
     * @return int
     */
    public function getTotalDonated()
    {
        return $this->totalDonated;
    }

    /**
     * Is user have donated.
     *
     * @return bool
     */
    public function isDonor()
    {
        return $this->getTotalDonated() > 0;
    }

    /**
     * Set vote.
     *
     * @param int $vote
     *
     * @return User
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get vote.
     *
     * @return int
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * Remove vote.
     *
     * @param int $vote
     *
     * @return User
     */
    public function removeVote($vote)
    {
        $this->vote -= $vote;

        return $this;
    }

    /**
     * Set displayName.
     *
     * @param bool $displayName
     *
     * @return User
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName.
     *
     * @return bool
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return User
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set realUserName.
     *
     * @param bool $realUserName
     *
     * @return User
     */
    public function setRealUserName($realUserName)
    {
        $this->realUserName = $realUserName;

        return $this;
    }

    /**
     * Get realUserName.
     *
     * @return bool
     */
    public function getRealUserName()
    {
        return $this->realUserName;
    }

    /**
     * Get name for display.
     *
     * @return string
     */
    public function getNameForDisplay()
    {
        if (null !== $this->getName() && '' !== $this->getName()) {
            return $this->getName();
        } elseif (1 == $this->getRealUserName()) {
            return $this->getUsername();
        }

        return $this->getEmail();
    }

    /**
     * Add display.
     *
     * @param \MediaConchOnlineBundle\Entity\DisplayFile $display
     *
     * @return User
     */
    public function addDisplay(\MediaConchOnlineBundle\Entity\DisplayFile $display)
    {
        $this->display[] = $display;

        return $this;
    }

    /**
     * Remove display.
     *
     * @param \MediaConchOnlineBundle\Entity\DisplayFile $display
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeDisplay(\MediaConchOnlineBundle\Entity\DisplayFile $display)
    {
        return $this->display->removeElement($display);
    }

    /**
     * Get display.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Set quotas.
     *
     * @param \UserBundle\Entity\UserQuotas|null $quotas
     *
     * @return User
     */
    public function setQuotas(?\UserBundle\Entity\UserQuotas $quotas = null)
    {
        $this->quotas = $quotas;

        return $this;
    }

    /**
     * Get quotas.
     *
     * @return \UserBundle\Entity\UserQuotas|null
     */
    public function getQuotas()
    {
        return $this->quotas;
    }

    /**
     * Set quotasDefault.
     *
     * @param \UserBundle\Entity\UserQuotasDefault|null $quotasDefault
     *
     * @return User
     */
    public function setQuotasDefault(?\UserBundle\Entity\UserQuotasDefault $quotasDefault = null)
    {
        $this->quotasDefault = $quotasDefault;

        return $this;
    }

    /**
     * Get quotasDefault.
     *
     * @return \UserBundle\Entity\UserQuotasDefault|null
     */
    public function getQuotasDefault()
    {
        return $this->quotasDefault;
    }

    /**
     * Add setting.
     *
     * @param \UserBundle\Entity\Settings $setting
     *
     * @return User
     */
    public function addSetting(\UserBundle\Entity\Settings $setting)
    {
        $this->settings[] = $setting;

        return $this;
    }

    /**
     * Remove setting.
     *
     * @param \UserBundle\Entity\Settings $setting
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeSetting(\UserBundle\Entity\Settings $setting)
    {
        return $this->settings->removeElement($setting);
    }

    /**
     * Get settings.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set guestToken.
     *
     * @param \UserBundle\Entity\GuestToken|null $guestToken
     *
     * @return User
     */
    public function setGuestToken(?\UserBundle\Entity\GuestToken $guestToken = null)
    {
        $this->guestToken = $guestToken;

        return $this;
    }

    /**
     * Get guestToken.
     *
     * @return \UserBundle\Entity\GuestToken|null
     */
    public function getGuestToken()
    {
        return $this->guestToken;
    }

    /**
     * Add apiKey.
     *
     * @param \UserBundle\Entity\ApiKey $apiKey
     *
     * @return User
     */
    public function addApiKey(\UserBundle\Entity\ApiKey $apiKey)
    {
        $this->apiKey[] = $apiKey;

        return $this;
    }

    /**
     * Remove apiKey.
     *
     * @param \UserBundle\Entity\ApiKey $apiKey
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeApiKey(\UserBundle\Entity\ApiKey $apiKey)
    {
        return $this->apiKey->removeElement($apiKey);
    }

    /**
     * Get apiKey.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Is user is active member.
     *
     * @return bool
     */
    public function isMember()
    {
        if ($this->hasRole('ROLE_ADMIN')) {
            return true;
        }

        return $this->getEndDate() > new \DateTime();
    }
}
