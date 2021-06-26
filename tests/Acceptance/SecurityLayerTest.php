<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Acceptance;

use Istio\Symfony\JWTAuthentication\Tests\Fixtures\TokenTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityLayerTest extends WebTestCase
{
    use TokenTrait;

    public function testRequestWithOriginToken(): void
    {
        $this->createClient()->request(
            'GET',
            '/secure',
            ['token' => $this->getOriginToken('issuer_1', 'id_1')]
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testRequestWithBase64Header(): void
    {
        $this->createClient()->request(
            'GET',
            '/secure',
            server: ['HTTP_X_ISTIO_JWT_PAYLOAD' => $this->getBase64Payload('issuer_2', 'id_2')]
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testRequestWithoutToken(): void
    {
        $this->createClient()->request('GET', '/secure');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRequestWithInvalidIssuer(): void
    {
        $this->createClient()->request(
            'GET',
            '/secure',
            ['token' => $this->getOriginToken('issuer_3', 'id_3')]
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRequestWithInvalidTokenType(): void
    {
        $this->createClient()->request(
            'GET',
            '/secure',
            ['token' => $this->getBase64Payload('issuer_1', 'id_1')]
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
