<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Security\Authenticator;

use Ory\Kratos\Client\ApiException;
use Ory\Kratos\Client\Model\Session;
use StethoMe\OryAuthBundle\Security\UserProvider\OryKratosUserProviderInterface;
use StethoMe\OryAuthBundle\Services\OryKratosClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class OryKratosAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly OryKratosClient $kratos,
        private readonly UserProviderInterface $userProvider,
        private readonly Security $security,
        private readonly bool $checkSession = true,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->security->getUser() ? $this->checkSession : null;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return $this->kratos->getLoginResponse($request);
    }

    /**
     * @throws ApiException
     */
    public function authenticate(Request $request): Passport
    {
        try {
            $this->stopwatch?->start('ory_kratos_session');
            $session = $this->kratos->getRequestSession($request);
        } catch (ApiException $exception) {
            if (Response::HTTP_UNAUTHORIZED === $exception->getCode()) {
                throw new AuthenticationException('Session is unauthorized', 0, $exception);
            }

            throw $exception;
        } finally {
            $this->stopwatch?->stop('ory_kratos_session');
        }

        // keep current user if we're only checking if the Ory Kratos session has not expired
        if ($this->checkSession && $user = $this->security->getUser()) {
            // handle user switching accounts outside our application
            if ($this->loadUser($session->getIdentity()->getId(), $session)->getUserIdentifier() !== $user->getUserIdentifier()) {
                throw new AuthenticationException('Session user not equal application user!');
            }

            return new SelfValidatingPassport(
                new UserBadge(
                    $user->getUserIdentifier(),
                    fn () => $user,
                )
            );
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $session->getIdentity()->getId(),
                function (string $userIdentifier) use ($session) {
                    return $this->loadUser($userIdentifier, $session);
                },
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->start($request, $exception);
    }

    protected function loadUser(string $userIdentifier, Session $session): UserInterface
    {
        if ($this->userProvider instanceof OryKratosUserProviderInterface) {
            return $this->userProvider->loadUserByIdentifierAndSession($userIdentifier, $session);
        }

        if ($this->userProvider instanceof ChainUserProvider) {
            foreach ($this->userProvider->getProviders() as $provider) {
                try {
                    if ($provider instanceof OryKratosUserProviderInterface) {
                        return $provider->loadUserByIdentifierAndSession($userIdentifier, $session);
                    }

                    return $provider->loadUserByIdentifier($userIdentifier);
                } catch (UserNotFoundException $e) {
                    // try next one
                }
            }

            $ex = new UserNotFoundException(sprintf('There is no user with identifier "%s".', $userIdentifier));
            $ex->setUserIdentifier($userIdentifier);

            throw $ex;
        }

        return $this->userProvider->loadUserByIdentifier($userIdentifier);
    }
}
