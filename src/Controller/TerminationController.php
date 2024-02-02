<?php

namespace App\Controller;

use App\Entity\Violation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TerminationController extends AbstractController
{
    #[Route('/terminated', name: 'app_terminated')]
    public function terminated(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->redirectToRoute('app_home_index');
        }

        $violation = $entityManager->getRepository(Violation::class)->findOneBy([
            'recipient' => $user,
            'type' => 'termination',
            'valid_until' => null
        ]);
        if (!$violation) {
            $violation = $entityManager->getRepository(Violation::class)->createQueryBuilder('v')
                ->where('v.recipient = :user')
                ->andWhere('v.type = :type')
                ->andWhere('v.valid_until > :now')
                ->setParameters([
                    'user' => $user,
                    'type' => 'termination',
                    'now' => new DateTimeImmutable('now', new \DateTimeZone('Europe/Berlin'))
                ])
                ->getQuery()
                ->getOneOrNullResult();
        }

        if (!$violation) {
            return $this->redirectToRoute('app_home_index');
        }

        return $this->render('termination/terminated.html.twig', [
            'violation' => $violation,
        ]);
    }
}