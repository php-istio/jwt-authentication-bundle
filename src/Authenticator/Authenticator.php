<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Authenticator;

use Istio\Symfony\JWTAuthentication\User\JWTPayloadAwareUserProviderInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class Authenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private iterable $userIdentifierClaimMappings,
        private UserProviderInterface $userProvider,
        private HttpMessageFactoryInterface $httpMessageFactory
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $psr7Request = $this->httpMessageFactory->createRequest($request);

        foreach ($this->userIdentifierClaimMappings as $mapping) {
            /** @var UserIdentifierClaimMapping $mapping */
            $payload = $mapping->extractor()->extract($psr7Request);

            if (null !== $payload && false !== is_string($payload[$mapping->userIdentifierClaim()] ?? null)) {
                $request->attributes->set(
                    '_user_identifier_claim_and_payload',
                    [
                        $mapping->userIdentifierClaim(),
                        $payload,
                    ]
                );

                return true;
            }
        }

        return false;
    }

    public function authenticate(Request $request): Passport
    {
        [$userIdentifierClaim, $payload] = $request->attributes->get('_user_identifier_claim_and_payload');
        $request->attributes->remove('_user_identifier_claim_and_payload');
        $userBadge = new UserBadge($payload[$userIdentifierClaim], $this->makeUserLoader($payload));
        $passport = new SelfValidatingPassport($userBadge);
        $passport->setAttribute('_payload', $payload);

        return $passport;
    }

    private function makeUserLoader(array $payload): callable
    {
        return function (string $userIdentifier) use ($payload) {
            if ($this->userProvider instanceof JWTPayloadAwareUserProviderInterface) {
                return $this->userProvider->loadUserByIdentifier($userIdentifier, $payload);
            }

            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        };
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        /** @var SelfValidatingPassport $passport */
        $payload = $passport->getAttribute('_payload');
        $token = parent::createToken($passport, $firewallName);
        $token->setAttribute('jwt_payload', $payload);

        return $token;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response('Istio JWT in request\'s missing or invalid.', Response::HTTP_UNAUTHORIZED);
    }
}
