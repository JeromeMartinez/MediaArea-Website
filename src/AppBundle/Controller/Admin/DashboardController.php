<?php

namespace AppBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('admin_user_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MediaArea Admin');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setPaginatorPageSize(50);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkTo(UserCrudController::class, 'User');
        yield MenuItem::linkTo(InvoiceCrudController::class, 'Invoice');
        yield MenuItem::linkTo(FeatureCrudController::class, 'Feature');
        yield MenuItem::linkTo(VoteCrudController::class, 'Vote');
        yield MenuItem::linkTo(UserQuotasCrudController::class, 'UserQuotas');
        yield MenuItem::linkTo(UserQuotasDefaultCrudController::class, 'UserQuotasDefault');
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Back to the website', null, 'homepage');
    }
}
