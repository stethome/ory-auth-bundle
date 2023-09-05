<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Services;

use Ory\Kratos\Client\Model\LogoutFlow;
use Ory\Kratos\Client\Model\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface OryKratosClientInterface
{
    public function __construct(
        string $kratosPublicUrl,
        string $kratosBrowserUrl,
        string $cookieName,
        ?\GuzzleHttp\ClientInterface $client,
    );

    public function getRequestSession(Request $request): Session;

    public function createLogoutFlow(Request $request): LogoutFlow;

    public function getLoginResponse(Request $request): Response;
}
