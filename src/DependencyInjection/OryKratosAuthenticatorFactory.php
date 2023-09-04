<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\DependencyInjection;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OryKratosAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    public function getPriority(): int
    {
        return 10;
    }

    public function getKey(): string
    {
        return 'ory_kratos';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('public_url')
                    ->isRequired()
                    ->info('The URL where Ory Kratos\'s Public API is located at. If this app and Ory Kratos are running in the same private network, this should be the private network address (e.g. kratos-public.svc.cluster.local)')
                ->end()
                ->scalarNode('browser_url')
                    ->info('The browser accessible URL where Ory Kratos\'s public API is located, only needed if it differs from public_url')
                ->end()
                ->scalarNode('session_cookie')
                    ->info('Name of the cookie holding Ory Kratos session')
                    ->defaultValue('ory_kratos_session')
                ->end()
                ->scalarNode('provider')
                    ->defaultNull()
                ->end()
                ->scalarNode('authenticator')
                    ->defaultValue('stethome.security.ory_kratos_authenticator')
                ->end()
            ->end()
        ;
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId)
    {
        $authenticatorId = 'security.authenticator.ory_kratos.'.$firewallName;
        $clientId = 'stethome.service.ory_kratos_client.'.$firewallName;

        $userProviderId = empty($config['provider']) ? $userProviderId : 'security.user.provider.concrete.'.$config['provider'];

        $container
            ->setDefinition($clientId, new ChildDefinition('stethome.service.ory_kratos_client'))
            ->setFactory([new Reference('stethome.factory.ory_kratos_client'), 'create'])
            ->replaceArgument(0, $config['public_url'])
            ->replaceArgument(1, $config['browser_url'] ?? $config['public_url'])
            ->replaceArgument(2, $config['session_cookie'])
        ;

        $container
            ->setDefinition($authenticatorId, new ChildDefinition($config['authenticator']))
            ->replaceArgument(0, new Reference($clientId))
            ->replaceArgument(1, new Reference($userProviderId))
        ;

        return $authenticatorId;
    }
}
