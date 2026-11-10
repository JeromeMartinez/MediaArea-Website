<?php

namespace AppBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PaymentBundle\Entity\Invoice;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class InvoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Invoice')
            ->setEntityLabelInPlural('Invoice')
            ->setSearchFields(['user.username', 'user.email', 'amount', 'vat', 'currency', 'ipAddress', 'country']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DETAIL, Action::DELETE);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX !== $pageName) {
            // Only the user of an invoice can be changed
            return [
                AssociationField::new('user')->autocomplete(),
            ];
        }

        return [
            AssociationField::new('user'),
            NumberField::new('amount')->setNumDecimals(2),
            NumberField::new('vat')->setNumDecimals(2),
            TextField::new('currency'),
            TextField::new('ipAddress'),
            TextField::new('country'),
            DateTimeField::new('date'),
            TextField::new('shortPaymentSystemName', 'Payment')->setSortable(false),
        ];
    }
}
