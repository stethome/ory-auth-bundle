<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Security\UserProvider;

use Ory\Kratos\Client\Model\Session;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface OryKratosUserProviderInterface extends UserProviderInterface
{
    public function loadUserByIdentifierAndSession(string $identifier, Session $session): UserInterface;
}
