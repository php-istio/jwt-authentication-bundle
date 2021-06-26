<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Unit\User;

use Istio\Symfony\JWTAuthentication\Tests\Fixtures\StatelessUser;
use Istio\Symfony\JWTAuthentication\User\JWTPayloadAwareUserProviderInterface;
use Istio\Symfony\JWTAuthentication\User\StatelessUserInterface;
use Istio\Symfony\JWTAuthentication\User\StatelessUserProvider;
use PHPUnit\Framework\TestCase;

class StatelessUserProviderTest extends TestCase
{
    public function testConstructor()
    {
        $provider = new StatelessUserProvider(StatelessUser::class);

        $this->assertInstanceOf(JWTPayloadAwareUserProviderInterface::class, $provider);

        $this->expectException(\InvalidArgumentException::class);

        new StatelessUserProvider('');
    }

    public function testLoadUserByIdentifier()
    {
        $provider = new StatelessUserProvider(StatelessUser::class);
        $payload = ['test'];
        $instance = $provider->loadUserByIdentifier('test', $payload);

        $this->assertInstanceOf(StatelessUser::class, $instance);
        $this->assertSame($payload, $instance->payload);

        $instanceCached = $provider->loadUserByIdentifier('test', $payload);

        $this->assertSame($instance, $instanceCached);
    }

    public function testThrowExceptionWhenLoadUserByIdentifierWithoutPayload()
    {
        $this->expectException(\LogicException::class);

        $provider = new StatelessUserProvider(StatelessUser::class);
        $provider->loadUserByIdentifier('test');
    }

    public function testRefreshUserReturnSameInstance()
    {
        $provider = new StatelessUserProvider(StatelessUser::class);
        $user = new StatelessUser();

        $this->assertSame($user, $provider->refreshUser($user));
    }

    public function testSupportOnlyStatelessUserInterface()
    {
        $provider = new StatelessUserProvider(StatelessUser::class);

        $this->assertTrue($provider->supportsClass(StatelessUserInterface::class));
        $this->assertFalse($provider->supportsClass(''));
    }
}
