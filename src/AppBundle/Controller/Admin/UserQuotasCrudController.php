<?php

namespace AppBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UserBundle\Entity\UserQuotas;

#[IsGranted('ROLE_ADMIN')]
class UserQuotasCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserQuotas::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('UserQuotas')
            ->setEntityLabelInPlural('UserQuotas');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::DETAIL);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user'),
            IntegerField::new('policies'),
            IntegerField::new('uploads'),
            DateTimeField::new('uploadsTimestamp'),
            IntegerField::new('urls'),
            DateTimeField::new('urlsTimestamp'),
            IntegerField::new('policyChecks'),
            DateTimeField::new('policyChecksTimestamp'),
        ];
    }
}
