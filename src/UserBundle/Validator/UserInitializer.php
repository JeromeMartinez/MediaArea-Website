<?php

namespace UserBundle\Validator;

use Symfony\Component\Validator\ObjectInitializerInterface;
use UserBundle\Entity\User;
use UserBundle\Lib\UserManager;

/**
 * Update the canonical fields before the validation, they are used by the uniqueness constraints.
 */
class UserInitializer implements ObjectInitializerInterface
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function initialize(object $object): void
    {
        if ($object instanceof User) {
            $this->userManager->updateCanonicalFields($object);
        }
    }
}
