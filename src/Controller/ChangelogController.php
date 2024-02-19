<?php

namespace App\Controller;

use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChangelogController extends AbstractController
{
    #[Route('/changelog', name: 'app_changelog')]
    public function index(Request $request): Response
    {
        $changelog = file_get_contents(__DIR__ . '/../../public/info/changelog.json');
        try {
            $changelog = json_decode($changelog, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $changelog = [];
        }

        $roadmap = file_get_contents(__DIR__ . '/../../public/info/roadmap.json');
        try {
            $roadmap = json_decode($roadmap, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $roadmap = [];
        }

        return $this->render('changelog/index.html.twig', [
            'changelog' => $changelog,
            'currentRoute' => $request->attributes->get('_route'),
            'roadmap' => $roadmap,
        ]);
    }
}