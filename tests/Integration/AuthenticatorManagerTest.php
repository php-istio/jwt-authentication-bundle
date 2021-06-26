<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Integration;

use Istio\Symfony\JWTAuthentication\Authenticator\Authenticator;
use Istio\Symfony\JWTAuthentication\Tests\Fixtures\StatelessUser;
use Istio\Symfony\JWTAuthentication\Tests\Fixtures\TokenTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

class AuthenticatorManagerTest extends KernelTestCase
{
    use TokenTrait;

    public function testAuthenticatorControlledByManager()
    {
        $kernel = $this->bootKernel();
        /** @var AuthenticatorManager $manager */
        $manager = $kernel->getContainer()->get('security.authenticator.manager.test');
        $request = Request::create(
            '',
            'GET',
            parameters: ['token' => $this->getOriginToken('issuer_1', 'id_1')]
        );

        $this->assertTrue($manager->supports($request));
        $this->assertTrue($request->attributes->has('_security_authenticators'));
        $this->assertTrue($request->attributes->has('_user_identifier_and_payload'));

        $controlledAuthenticators = $request->attributes->get('_security_authenticators');

        $this->assertCount(1, $controlledAuthenticators);
        $this->assertInstanceOf(Authenticator::class, $controlledAuthenticators[0]);
    }

    /**
     * @depends testAuthenticatorControlledByManager
     */
    public function testLoadUserByJWTPayloadAwareProvider()
    {
        $kernel = $this->bootKernel();
        /** @var AuthenticatorManager $manager */
        $manager = $kernel->getContainer()->get('security.authenticator.manager.test');
        $request = Request::create(
            '',
            'GET',
            parameters: ['token' => $this->getOriginToken('issuer_1', 'id_1')]
        );

        $this->assertTrue($manager->supports($request));
        $this->assertNull($manager->authenticateRequest($request));

        $token = $kernel->getContainer()->get('security.token_storage')->getToken();
        $payload = $token->getAttribute('jwt_payload');

        $this->assertInstanceOf(StatelessUser::class, $token->getUser());
        $this->assertIsArray($payload);
        $this->assertSame('issuer_1', $payload['iss']);
        $this->assertSame('valid', $payload['id_1']);
    }

    /**
     * @depends testAuthenticatorControlledByManager
     */
    public function testLoadUserByMemoryProvider()
    {
        $kernel = $this->bootKernel();
        /** @var AuthenticatorManager $manager */
        $manager = $kernel->getContainer()->get('security.authenticator.manager.test2');
        $request = Request::create(
            '',
            'GET',
            server: ['HTTP_AUTHORIZATION' => $this->getOriginToken('issuer_2', 'id_2')]
        );

        $this->assertTrue($manager->supports($request));
        $this->assertNull($manager->authenticateRequest($request));

        $token = $kernel->getContainer()->get('security.token_storage')->getToken();
        $payload = $token->getAttribute('jwt_payload');

        $this->assertInstanceOf(InMemoryUser::class, $token->getUser());
        $this->assertIsArray($payload);
        $this->assertSame('issuer_2', $payload['iss']);
        $this->assertSame('valid', $token->getUser()->getUserIdentifier());
    }
}
