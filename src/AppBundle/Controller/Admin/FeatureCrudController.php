<?php

namespace AppBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use VoteBundle\Entity\Feature;

#[IsGranted('ROLE_ADMIN')]
class FeatureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feature::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Feature')
            ->setEntityLabelInPlural('Feature')
            ->setSearchFields(['title', 'description']);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('title'),
                IntegerField::new('votesTarget'),
                IntegerField::new('votesCountCache'),
                IntegerField::new('status'),
            ];
        }

        return [
            TextField::new('title'),
            TextareaField::new('description'),
            IntegerField::new('votesTarget')->setFormTypeOption('attr', ['min' => 0]),
            IntegerField::new('votesCountCache')->setFormTypeOption('attr', ['min' => 0]),
            IntegerField::new('status')
                ->setFormTypeOption('attr', ['min' => 0, 'max' => 3])
                ->setHelp('Close : 0 - Open : 1 - In progress : 2 - Finish : 3'),
        ];
    }
}
