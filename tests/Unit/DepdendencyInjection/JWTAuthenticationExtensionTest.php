<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Unit\DepdendencyInjection;

use Istio\Symfony\JWTAuthentication\DependencyInjection\JWTAuthenticationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JWTAuthenticationExtensionTest extends TestCase
{
    /**
     * @dataProvider registeredDefinitions
     */
    public function testCanLoadService(string $id)
    {
        $container = new ContainerBuilder();
        $extension = new JWTAuthenticationExtension();
        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition($id));
    }

    public function registeredDefinitions(): array
    {
        return [
            ['istio.jwt_authentication.authenticator'],
            ['istio.jwt_authentication.stateless_user_provider'],
            ['istio.jwt_authentication.payload_extractor.composite'],
            ['istio.jwt_authentication.payload_extractor.origin_token.header'],
            ['istio.jwt_authentication.payload_extractor.origin_token.query_param'],
            ['istio.jwt_authentication.payload_extractor.base64_header'],
        ];
    }
}
