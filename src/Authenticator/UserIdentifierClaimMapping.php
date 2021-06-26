<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Authenticator;

use Istio\JWTPayloadExtractor\ExtractorInterface;

final class UserIdentifierClaimMapping
{
    public function __construct(private string $userIdentifierClaim, private ExtractorInterface $extractor)
    {
    }

    public function userIdentifierClaim(): string
    {
        return $this->userIdentifierClaim;
    }

    public function extractor(): ExtractorInterface
    {
        return $this->extractor;
    }
}
