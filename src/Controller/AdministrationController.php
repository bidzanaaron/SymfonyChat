<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdministrationController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request): Response
    {
        return $this->render('administration/index.html.twig', [
            'currentRoute' => $request->attributes->get('_route'),
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usersPerPage = 5;
        $page = $request->query->get('page', 1);

        $users = $entityManager->getRepository(User::class)->findBy([], ['id' => 'DESC'], $usersPerPage, ($page - 1) * $usersPerPage);

        $userCount = $entityManager->getRepository(User::class)->count([]);

        return $this->render('administration/users.html.twig', [
            'currentRoute' => $request->attributes->get('_route'),
            'users' => $users,
            'maxPage' => ceil($userCount / $usersPerPage),
            'currentPage' => $page,
        ]);
    }
}