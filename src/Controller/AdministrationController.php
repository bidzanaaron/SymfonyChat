<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Violation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
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

    #[Route('/admin/violations', name: 'app_admin_violations')]
    public function violations(Request $request, EntityManagerInterface $entityManager): Response
    {
        $violationsPerPage = 5;
        $page = $request->query->get('page', 1);

        $violations = $entityManager->getRepository(Violation::class)->findBy(['type' => 'warning'], ['id' => 'DESC'], $violationsPerPage, ($page - 1) * $violationsPerPage);

        $violationCount = $entityManager->getRepository(Violation::class)->count(['type' => 'warning']);

        return $this->render('administration/violations.html.twig', [
            'currentRoute' => $request->attributes->get('_route'),
            'violations' => $violations,
            'maxPage' => ceil($violationCount / $violationsPerPage),
            'currentPage' => $page,
        ]);
    }

    #[Route('/admin/terminations', name: 'app_admin_terminations')]
    public function terminations(Request $request, EntityManagerInterface $entityManager): Response
    {
        $terminationsPerPage = 5;
        $page = $request->query->get('page', 1);

        $terminations = $entityManager->getRepository(Violation::class)->findBy(['type' => 'termination'], ['id' => 'DESC'], $terminationsPerPage, ($page - 1) * $terminationsPerPage);

        $terminationCount = $entityManager->getRepository(Violation::class)->count(['type' => 'termination']);

        return $this->render('administration/terminations.html.twig', [
            'currentRoute' => $request->attributes->get('_route'),
            'terminations' => $terminations,
            'maxPage' => ceil($terminationCount / $terminationsPerPage),
            'currentPage' => $page,
        ]);
    }
}