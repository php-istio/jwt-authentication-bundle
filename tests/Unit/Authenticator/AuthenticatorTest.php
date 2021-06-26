<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Unit\Authenticator;

use Istio\JWTPayloadExtractor\ExtractorFactory;
use Istio\Symfony\JWTAuthentication\Authenticator\Authenticator;
use Istio\Symfony\JWTAuthentication\Authenticator\UserIdentifierClaimMapping;
use Istio\Symfony\JWTAuthentication\Tests\Fixtures\TokenTrait;
use Istio\Symfony\JWTAuthentication\User\JWTPayloadAwareUserProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AuthenticatorTest extends TestCase
{
    use TokenTrait;

    /**
     * @dataProvider validRequests
     */
    public function testSupportValidRequest(Request $request)
    {
        $authenticator = $this->createAuthenticator(new InMemoryUserProvider());

        $this->assertTrue($authenticator->supports($request));
        $this->assertTrue($request->attributes->has('_user_identifier_and_payload'));
    }

    /**
     * @dataProvider invalidRequests
     */
    public function testNotSupportInvalidRequest(Request $request)
    {
        $authenticator = $this->createAuthenticator(new InMemoryUserProvider());

        $this->assertFalse($authenticator->supports($request));
        $this->assertFalse($request->attributes->has('_user_identifier_and_payload'));
    }

    /**
     * @dataProvider validRequests
     * @depends      testSupportValidRequest
     */
    public function testAuthenticate(Request $request)
    {
        $authenticator = $this->createAuthenticatorWithInMemoryUserProvider(['valid' => []]);
        $authenticator->supports($request); // call to set `_user_identifier_and_payload` request attribute.

        $passport = $authenticator->authenticate($request);

        $this->assertFalse($request->attributes->has('_user_identifier_and_payload'));
        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertTrue($passport->hasBadge(UserBadge::class));
        $this->assertIsArray($passport->getAttribute('_payload'));

        /** @var UserBadge $userBadge */
        $userBadge = $passport->getBadge(UserBadge::class);
        $this->assertInstanceOf(InMemoryUser::class, $userBadge->getUser());
        $this->assertSame('valid', $userBadge->getUser()->getUserIdentifier());
    }

    /**
     * @dataProvider validRequests
     * @depends      testAuthenticate
     */
    public function testAuthenticateWithJWTPayloadAwareUserProvider(Request $request)
    {
        $payload = [];
        $authenticator = $this->createAuthenticatorWithJWTPayloadAwareUserProvider($payload);
        $authenticator->supports($request); // call to set `_user_identifier_and_payload` request attribute.
        $payload = $request->attributes->get(
            '_user_identifier_and_payload'
        )[1]; // set for mock assert see: [[createAuthenticatorWithJWTPayloadAwareUserProvider()]]
        $passport = $authenticator->authenticate($request);
        $user = $passport->getBadge(UserBadge::class)->getUser();

        $this->assertInstanceOf(UserInterface::class, $user);
    }

    /**
     * @dataProvider validRequests
     * @depends      testAuthenticate
     */
    public function testCreateAuthenticatedToken(Request $request)
    {
        $authenticator = $this->createAuthenticatorWithInMemoryUserProvider(['valid' => []]);
        $authenticator->supports($request);
        $payload = $request->attributes->get('_user_identifier_and_payload')[1];
        $passport = $authenticator->authenticate($request);
        $token = $authenticator->createAuthenticatedToken($passport, 'test');

        $this->assertTrue($token->hasAttribute('jwt_payload'));
        $this->assertSame($payload, $token->getAttribute('jwt_payload'));
    }

    public function testEntrypoint()
    {
        $authenticator = $this->createAuthenticatorWithInMemoryUserProvider(['valid' => []]);
        $response = $authenticator->start(Request::create('', 'GET'));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function validRequests(): array
    {
        return [
            [
                Request::create(
                    '',
                    'GET',
                    server: [
                        'HTTP_AUTHORIZATION' => $this->getOriginToken('issuer_1', 'id_1'),
                    ]
                ),
            ],
            [
                Request::create(
                    '',
                    'GET',
                    parameters: [
                        'token' => $this->getOriginToken('issuer_2', 'id_2'),
                    ]
                ),
            ],
            [
                Request::create(
                    '',
                    'GET',
                    server: [
                        'HTTP_X_ISTIO_JWT_PAYLOAD' => $this->getBase64Payload('issuer_3', 'id_3'),
                    ]
                ),
            ],
        ];
    }

    public function invalidRequests(): array
    {
        return [
            [
                Request::create(
                    '',
                    'GET',
                    server: [
                        'HTTP_AUTHORIZATION' => $this->getOriginToken('issuer_1', 'id_2'),
                    ]
                ),
            ],
            [
                Request::create(
                    '',
                    'GET',
                    parameters: [
                        'token' => $this->getOriginToken('issuer_2', 'id_1'),
                    ]
                ),
            ],
            [
                Request::create(
                    '',
                    'GET',
                    server: [
                        'HTTP_X_ISTIO_JWT_PAYLOAD' => $this->getBase64Payload('issuer_3', 'id_1'),
                    ]
                ),
            ],
            [
                Request::create(
                    '',
                    'GET',
                    server: [
                        'HTTP_AUTHORIZATION' => $this->getOriginToken('issuer_1', 'id_1', []),
                    ]
                ),
            ],
        ];
    }

    private function createAuthenticatorWithJWTPayloadAwareUserProvider(array &$expectedPayload): Authenticator
    {
        $provider = $this->createMock(JWTPayloadAwareUserProviderInterface::class);
        $provider->method('loadUserByIdentifier')->willReturnCallback(
            function (string $userIdentifier, array $payload) use (&$expectedPayload) {
                $this->assertSame($payload, $expectedPayload);

                return $this->createMock(UserInterface::class);
            }
        );

        return $this->createAuthenticator($provider);
    }

    private function createAuthenticatorWithInMemoryUserProvider(array $users): Authenticator
    {
        return $this->createAuthenticator(new InMemoryUserProvider($users));
    }

    private function createAuthenticator(UserProviderInterface $userProvider): Authenticator
    {
        return new Authenticator($this->getUserIdentifierClaimMappings(), $userProvider);
    }

    private function getUserIdentifierClaimMappings(): array
    {
        return [
            new UserIdentifierClaimMapping(
                'id_1',
                ExtractorFactory::fromOriginTokenHeader('issuer_1', 'authorization')
            ),
            new UserIdentifierClaimMapping(
                'id_2',
                ExtractorFactory::fromOriginTokenQueryParam('issuer_2', 'token')
            ),
            new UserIdentifierClaimMapping(
                'id_3',
                ExtractorFactory::fromBase64Header('issuer_3', 'x-istio-jwt-payload')
            ),
        ];
    }
}
