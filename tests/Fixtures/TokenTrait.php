<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Fixtures;

trait TokenTrait
{
    private function getOriginToken(
        string $issuer,
        string $userIdentifierClaim,
        mixed $userIdentifier = 'valid'
    ): string {
        return sprintf(
            'Bearer header.%s.signature',
            $this->getBase64Payload($issuer, $userIdentifierClaim, $userIdentifier)
        );
    }

    private function getBase64Payload(
        string $issuer,
        string $userIdentifierClaim,
        mixed $userIdentifier = 'valid'
    ): string {
        return base64_encode(json_encode(['iss' => $issuer, $userIdentifierClaim => $userIdentifier]));
    }
}
