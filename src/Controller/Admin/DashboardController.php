<?php

namespace App\Controller\Admin;

use App\Entity\BookingRequest;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\BookingRequestRequest;
use App\Entity\House;
use App\Entity\User;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('admin_user_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('FEIP Backend');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Requests', 'fa fa-envelope', BookingRequest::class)->setController(BookingRequestCrudController::class);
        yield MenuItem::linkToCrud('Houses', 'fa fa-envelope', House::class)->setController(HouseCrudController::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-envelope', User::class)->setController(UserCrudController::class);
    }
}
