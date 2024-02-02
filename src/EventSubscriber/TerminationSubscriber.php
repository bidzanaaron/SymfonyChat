<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Violation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\LocaleSwitcher;

class TerminationSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $entityManager;

    protected Security $security;

    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @param LocaleSwitcher $localeSwitcher
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $entityManager, Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['checkIfTerminated', 0]
        ];
    }

    public function checkIfTerminated(RequestEvent $event): void
    {
        // If the user is not logged in, we don't need to check if the user is terminated
        if (!$this->security->getUser()) {
            return;
        }

        $user = $this->security->getUser();

        $violation = $this->entityManager->getRepository(Violation::class)->findOneBy([
            'recipient' => $user,
            'type' => 'termination',
            'valid_until' => null
        ]);
        if (!$violation) {
            $violation = $this->entityManager->getRepository(Violation::class)->createQueryBuilder('v')
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
            return;
        }

        if ($event->getRequest()->attributes->get('_route') === 'app_terminated') {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_terminated')));
    }
}