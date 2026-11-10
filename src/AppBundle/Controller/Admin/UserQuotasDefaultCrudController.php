<?php

namespace AppBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UserBundle\Entity\UserQuotasDefault;

#[IsGranted('ROLE_ADMIN')]
class UserQuotasDefaultCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserQuotasDefault::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('UserQuotasDefault')
            ->setEntityLabelInPlural('UserQuotasDefault');
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function configureFields(string $pageName): iterable
    {
        $user = AssociationField::new('user');
        if (Crud::PAGE_NEW === $pageName) {
            $user->autocomplete();
        } elseif (Crud::PAGE_EDIT === $pageName) {
            // The user of existing quotas can not be changed
            $user->setDisabled();
        }

        return [
            $user,
            IntegerField::new('policies'),
            IntegerField::new('uploads'),
            IntegerField::new('urls'),
            IntegerField::new('policyChecks'),
        ];
    }
}
