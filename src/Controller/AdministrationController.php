<?php

namespace App\Controller;

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
}