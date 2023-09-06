<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Services;

use Ory\Kratos\Client\ApiException;
use Ory\Kratos\Client\Configuration;
use Ory\Kratos\Client\Model\LogoutFlow;
use Ory\Kratos\Client\Model\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OryKratosClient implements OryKratosClientInterface
{
    public const LOGIN_PATH = 'login';
    public const RETURN_PARAM = 'return_to';

    protected \Ory\Kratos\Client\Api\FrontendApi $frontendApi;

    public function __construct(
        string $kratosPublicUrl,
        protected string $kratosBrowserUrl,
        protected string $cookieName,
        ?\GuzzleHttp\ClientInterface $client,
    ) {
        $config = (new Configuration())->setHost($kratosPublicUrl);
        $this->frontendApi = new \Ory\Kratos\Client\Api\FrontendApi($client, $config);
    }

    /**
     * @throws ApiException
     */
    public function getRequestSession(Request $request): Session
    {
        return $this->frontendApi->toSession('', $this->getSessionCookie($request));
    }

    public function getLoginResponse(Request $request): Response
    {
        $loginPath = self::LOGIN_PATH;
        $returnParam = self::RETURN_PARAM;
        $returnPath = $request->getUri();

        return new RedirectResponse("{$this->kratosBrowserUrl}/{$loginPath}?{$returnParam}={$returnPath}");
    }

    /**
     * @throws ApiException
     */
    public function createLogoutFlow(Request $request): LogoutFlow
    {
        $returnTo = $request->getUriForPath('/');

        return $this->frontendApi->createBrowserLogoutFlow($this->getSessionCookie($request), $returnTo);
    }

    protected function getSessionCookie(Request $request): string
    {
        $cookieValue = $request->cookies->get($this->cookieName);

        return "{$this->cookieName}={$cookieValue}";
    }
}
