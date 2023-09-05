<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\EventListener;

use StethoMe\OryAuthBundle\Services\OryKratosClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    public function __construct(private OryKratosClientInterface $kratos)
    {
    }

    public function __invoke(LogoutEvent $event): void
    {
        $logoutFlow = $this->kratos->createLogoutFlow($event->getRequest());

        $response = new RedirectResponse($logoutFlow->getLogoutUrl());

        $event->setResponse($response);
    }
}
