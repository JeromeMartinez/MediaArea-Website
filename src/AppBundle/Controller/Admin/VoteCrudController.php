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
use VoteBundle\Entity\Vote;

#[IsGranted('ROLE_ADMIN')]
class VoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vote::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Vote')
            ->setEntityLabelInPlural('Vote')
            ->setSearchFields(['user.username', 'user.email', 'points']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DETAIL, Action::DELETE, Action::EDIT);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user'),
            IntegerField::new('points'),
            DateTimeField::new('date'),
            AssociationField::new('feature'),
        ];
    }
}
