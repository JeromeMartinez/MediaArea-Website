<?php

namespace AppBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UserBundle\Entity\User;
use UserBundle\Form\Type\CountryCustomType;
use UserBundle\Form\Type\LanguageCustomType;
use UserBundle\Form\Type\ProfessionalType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('User')
            ->setSearchFields(['username', 'email', 'name']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('username'),
                EmailField::new('email'),
                TextField::new('name'),
                BooleanField::new('enabled'),
                DateTimeField::new('createdAt'),
                DateTimeField::new('lastLogin'),
                IntegerField::new('totalDonated', 'Total'),
                IntegerField::new('vote'),
            ];
        }

        $fields = [
            TextField::new('username'),
            EmailField::new('email'),
            TextField::new('name'),
            TextField::new('companyName'),
            BooleanField::new('newsletter'),
            Field::new('professional')->setFormType(ProfessionalType::class),
            Field::new('country')->setFormType(CountryCustomType::class),
            Field::new('language')->setFormType(LanguageCustomType::class),
            BooleanField::new('enabled'),
            IntegerField::new('totalDonated'),
            IntegerField::new('vote'),
            BooleanField::new('displayName'),
            DateTimeField::new('endDate'),
            ArrayField::new('roles'),
        ];

        if (Crud::PAGE_DETAIL === $pageName) {
            $fields[] = DateTimeField::new('createdAt');
            $fields[] = DateTimeField::new('lastLogin');
        }

        return $fields;
    }
}
