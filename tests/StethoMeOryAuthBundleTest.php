<?php

declare(strict_types=1);

namespace StethoMe\OryAuthBundle\Tests;

use PHPUnit\Framework\TestCase;
use StethoMe\OryAuthBundle\DependencyInjection\StethoMeOryAuthExtension;
use StethoMe\OryAuthBundle\StethoMeOryAuthBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @internal
 *
 * @covers \StethoMe\OryAuthBundle\StethoMeOryAuthBundle
 */
final class StethoMeOryAuthBundleTest extends TestCase
{
    public function testInstance(): void
    {
        static::assertInstanceOf(Bundle::class, new StethoMeOryAuthBundle());
    }

    public function testGetContainerExtension(): void
    {
        $bundle = new StethoMeOryAuthBundle();

        $extension = $bundle->getContainerExtension();
        static::assertInstanceOf(StethoMeOryAuthExtension::class, $extension);

        // assert that on each call new extension is not created
        static::assertSame($extension, $bundle->getContainerExtension());
    }
}
