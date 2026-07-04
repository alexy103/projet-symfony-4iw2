<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Excusatron 3000 — Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Communauté');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user');
        yield MenuItem::linkTo(BadgeCrudController::class, 'Badges', 'fa fa-medal');
        yield MenuItem::linkTo(NotificationCrudController::class, 'Notifications', 'fa fa-bell');

        yield MenuItem::section('Contenu des excuses');
        yield MenuItem::linkTo(ExcuseCategoryCrudController::class, 'Catégories', 'fa fa-folder');
        yield MenuItem::linkTo(ExcuseContextCrudController::class, 'Contextes', 'fa fa-location-dot');
        yield MenuItem::linkTo(ExcuseToneCrudController::class, 'Tons', 'fa fa-masks-theater');
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fa fa-tag');

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', '/');
    }
}
