<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use StethoMe\OryAuthBundle\DependencyInjection\OryKratosAuthenticatorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection as SecurityBundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 *
 * @covers \StethoMe\OryAuthBundle\DependencyInjection\OryKratosAuthenticatorFactory::addConfiguration
 */
final class ConfigurationTest extends TestCase
{
    private static array $minimalConfig = [
        'firewalls' => [
            'main' => [
                'ory_kratos' => [
                    'public_url' => '//kratos.local',
                ],
            ],
        ],
    ];

    public function testNoPublicUrlConfigured(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/The child config "public_url" under "security.firewalls.main.ory_kratos" must be configured.*/');
        $config = [
            'firewalls' => [
                'main' => [
                    'ory_kratos' => [],
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new SecurityBundle\MainConfiguration([new OryKratosAuthenticatorFactory()], []);

        $processor->processConfiguration($configuration, [$config]);
    }

    public function testMinimalConfiguration(): void
    {
        $processor = new Processor();
        $configuration = new SecurityBundle\MainConfiguration([new OryKratosAuthenticatorFactory()], []);

        $processedConfig = $processor->processConfiguration($configuration, [static::$minimalConfig]);

        static::assertEquals('//kratos.local', $processedConfig['firewalls']['main']['ory_kratos']['public_url']);
        static::assertArrayNotHasKey('browser_url', $processedConfig['firewalls']['main']['ory_kratos']);
        static::assertEquals('ory_kratos_session', $processedConfig['firewalls']['main']['ory_kratos']['session_cookie']);
        static::assertTrue($processedConfig['firewalls']['main']['ory_kratos']['session_check']);
        static::assertNull($processedConfig['firewalls']['main']['ory_kratos']['provider']);
        static::assertEquals('stethome.security.ory_kratos_authenticator', $processedConfig['firewalls']['main']['ory_kratos']['authenticator']);
    }

    public function testFullConfiguration(): void
    {
        $config = [
            'firewalls' => [
                'main' => [
                    'ory_kratos' => [
                        'browser_url' => '//kratos.test',
                        'session_cookie' => 'test_session_cookie',
                        'session_check' => false,
                        'provider' => 'test.security.stub_provider',
                        'authenticator' => 'test.security.stub_authenticator',
                    ],
                ],
            ],
        ];
        $config = array_merge_recursive(static::$minimalConfig, $config);

        $processor = new Processor();
        $configuration = new SecurityBundle\MainConfiguration([new OryKratosAuthenticatorFactory()], []);

        $processedConfig = $processor->processConfiguration($configuration, [$config]);

        static::assertEquals('//kratos.local', $processedConfig['firewalls']['main']['ory_kratos']['public_url']);
        static::assertEquals('//kratos.test', $processedConfig['firewalls']['main']['ory_kratos']['browser_url']);
        static::assertEquals('test_session_cookie', $processedConfig['firewalls']['main']['ory_kratos']['session_cookie']);
        static::assertFalse($processedConfig['firewalls']['main']['ory_kratos']['session_check']);
        static::assertEquals('test.security.stub_provider', $processedConfig['firewalls']['main']['ory_kratos']['provider']);
        static::assertEquals('test.security.stub_authenticator', $processedConfig['firewalls']['main']['ory_kratos']['authenticator']);
    }
}
