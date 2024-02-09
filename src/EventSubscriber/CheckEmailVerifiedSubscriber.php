<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckEmailVerifiedSubscriber implements EventSubscriberInterface
{
    protected Security $security;
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'checkEmailVerified',
        ];
    }

    public function checkEmailVerified(RequestEvent $event)
    {
        if (!$this->security->getUser()) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user->isEmailVerified()) {
            $this->security->logout(false);

            $event->getRequest()->getSession()->getFlashBag()->add('error', 'You must verify your email to log into your account.');

            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
        }
    }
}