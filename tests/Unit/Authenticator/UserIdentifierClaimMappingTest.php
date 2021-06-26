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
use Istio\Symfony\JWTAuthentication\Authenticator\UserIdentifierClaimMapping;
use PHPUnit\Framework\TestCase;

class UserIdentifierClaimMappingTest extends TestCase
{
    public function testMapping()
    {
        $extractor = ExtractorFactory::fromExtractors();
        $instance = new UserIdentifierClaimMapping('id', $extractor);

        $this->assertSame('id', $instance->userIdentifierClaim());
        $this->assertSame($extractor, $instance->extractor());
    }
}
