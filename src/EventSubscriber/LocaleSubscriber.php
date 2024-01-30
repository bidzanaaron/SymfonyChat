<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

class LocaleSubscriber implements EventSubscriberInterface
{
    protected LocaleSwitcher $localeSwitcher;

    protected Security $security;

    /**
     * @param LocaleSwitcher $localeSwitcher
     * @param Security $security
     */
    public function __construct(LocaleSwitcher $localeSwitcher, Security $security)
    {
        $this->localeSwitcher = $localeSwitcher;
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['changeLocaleListener', 0]
        ];
    }

    public function changeLocaleListener(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $this->localeSwitcher->setLocale($user->getLanguage());
        }
    }
}