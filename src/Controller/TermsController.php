<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TermsController extends AbstractController
{
    #[Route('/terms', name: 'app_terms')]
    public function index(): Response
    {
        return $this->render('terms/index.html.twig');
    }

    #[Route('/terms/privacy', name: 'app_terms_privacy')]
    public function privacy(): Response
    {
        return $this->render('terms/privacy.html.twig');
    }

    #[Route('/terms/contact', name: 'app_terms_contact')]
    public function contact(): Response
    {
        return $this->render('terms/contact.html.twig');
    }
}