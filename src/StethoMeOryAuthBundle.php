<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle;

use StethoMe\OryAuthBundle\DependencyInjection\OryKratosAuthenticatorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class StethoMeOryAuthBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');

        // Authenticator factory for Symfony 5.4 and later
        if (method_exists($extension, 'addAuthenticatorFactory')) {
            $extension->addAuthenticatorFactory(new OryKratosAuthenticatorFactory());
        }
    }
}
