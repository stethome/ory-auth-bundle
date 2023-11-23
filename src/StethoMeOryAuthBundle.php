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
        $extension->addAuthenticatorFactory(new OryKratosAuthenticatorFactory());
    }
}
