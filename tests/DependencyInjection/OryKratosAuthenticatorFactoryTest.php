<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use StethoMe\OryAuthBundle\DependencyInjection\OryKratosAuthenticatorFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 *
 * @covers \StethoMe\OryAuthBundle\DependencyInjection\OryKratosAuthenticatorFactory
 */
final class OryKratosAuthenticatorFactoryTest extends TestCase
{
    private static array $minimalConfig = [
        'public_url' => '//kratos.local',
    ];

    public function testMinimalConfiguration(): void
    {
        $container = new ContainerBuilder();

        $factory = new OryKratosAuthenticatorFactory();
        $processedConfig = $this->processConfig(static::$minimalConfig, $factory);

        $factory->createAuthenticator($container, 'test_firewall', $processedConfig, 'test_userprovider');

        static::assertTrue($container->hasDefinition('stethome.service.ory_kratos_client.test_firewall'));
        static::assertEquals([
            'index_0' => '//kratos.local',
            'index_1' => '//kratos.local',
            'index_2' => 'ory_kratos_session',
        ], $container->getDefinition('stethome.service.ory_kratos_client.test_firewall')->getArguments());

        static::assertTrue($container->hasDefinition('security.authenticator.ory_kratos.test_firewall'));
        static::assertEquals(
            'stethome.security.ory_kratos_authenticator',
            $container->getDefinition('security.authenticator.ory_kratos.test_firewall')->getParent(),
        );
        static::assertEquals([
            'index_0' => new Reference('stethome.service.ory_kratos_client.test_firewall'),
            'index_1' => new Reference('test_userprovider'),
            'index_4' => true,
        ], $container->getDefinition('security.authenticator.ory_kratos.test_firewall')->getArguments());

        static::assertFalse($container->hasDefinition('security.logout_listener.ory_kratos.test_firewall'));
    }

    public function testFullConfiguration(): void
    {
        $container = new ContainerBuilder();
        $config = array_merge_recursive(static::$minimalConfig, [
            'browser_url' => '//kratos.test',
            'session_cookie' => 'test_session_cookie',
            'session_check' => false,
            'provider' => 'test.security.stub_provider',
            'authenticator' => 'test.security.stub_authenticator',
        ]);

        $factory = new OryKratosAuthenticatorFactory();
        $processedConfig = $this->processConfig($config, $factory);

        $factory->createAuthenticator($container, 'test_firewall', $processedConfig, 'test_userprovider');

        static::assertTrue($container->hasDefinition('stethome.service.ory_kratos_client.test_firewall'));
        static::assertEquals([
            'index_0' => '//kratos.local',
            'index_1' => '//kratos.test',
            'index_2' => 'test_session_cookie',
        ], $container->getDefinition('stethome.service.ory_kratos_client.test_firewall')->getArguments());

        static::assertTrue($container->hasDefinition('security.authenticator.ory_kratos.test_firewall'));
        static::assertEquals(
            'test.security.stub_authenticator',
            $container->getDefinition('security.authenticator.ory_kratos.test_firewall')->getParent(),
        );
        static::assertEquals([
            'index_0' => new Reference('stethome.service.ory_kratos_client.test_firewall'),
            'index_1' => new Reference('security.user.provider.concrete.test.security.stub_provider'),
            'index_4' => false,
        ], $container->getDefinition('security.authenticator.ory_kratos.test_firewall')->getArguments());

        static::assertFalse($container->hasDefinition('security.logout_listener.ory_kratos.test_firewall'));
    }

    public function testLogoutListener(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('security.logout_listener.test_firewall', new Definition());

        $factory = new OryKratosAuthenticatorFactory();
        $processedConfig = $this->processConfig(static::$minimalConfig, $factory);

        $factory->createAuthenticator($container, 'test_firewall', $processedConfig, 'test_userprovider');

        static::assertTrue($container->hasDefinition('security.logout_listener.ory_kratos.test_firewall'));
        static::assertEquals([
            'index_0' => new Reference('stethome.service.ory_kratos_client.test_firewall'),
        ], $container->getDefinition('security.logout_listener.ory_kratos.test_firewall')->getArguments());
        static::assertEquals([
            'kernel.event_listener' => [0 => []],
        ], $container->getDefinition('security.logout_listener.ory_kratos.test_firewall')->getTags());
    }

    private function processConfig(array $config, OryKratosAuthenticatorFactory $factory)
    {
        $rootNode = new ArrayNodeDefinition('ory_kratos');
        $factory->addConfiguration($rootNode);

        $node = $rootNode->getNode();
        $normalizedConfig = $node->normalize($config);

        return $node->finalize($normalizedConfig);
    }
}
