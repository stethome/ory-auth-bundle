<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Services;

use Ory\Kratos\Client\Model\Session;
use Symfony\Component\HttpFoundation\Request;

interface OryKratosClientInterface
{
    public function __construct(
        string $kratosPublicUrl,
        string $kratosBrowserUrl,
        string $cookieName,
        ?\GuzzleHttp\ClientInterface $client,
    );

    public function getRequestSession(Request $request): Session;
}
