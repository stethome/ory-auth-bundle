<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Services;

final class OryKratosClientFactory
{
    public function __construct(
        private readonly ?\GuzzleHttp\ClientInterface $guzzle,
    ) {
    }

    public function create(string $kratosPublicUrl, string $kratosInternalUrl, string $cookieName): OryKratosClientInterface
    {
        return new OryKratosClient($kratosPublicUrl, $kratosInternalUrl, $cookieName, $this->guzzle);
    }
}
