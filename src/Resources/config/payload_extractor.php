<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Istio\JWTPayloadExtractor\Base64HeaderExtractor;
use Istio\JWTPayloadExtractor\CompositeExtractor;
use Istio\JWTPayloadExtractor\ExtractorFactory;
use Istio\JWTPayloadExtractor\OriginTokenExtractor;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('istio.jwt_authentication.payload_extractor.composite', CompositeExtractor::class)
        ->abstract()
        ->factory([ExtractorFactory::class, 'fromExtractors'])

        ->set('istio.jwt_authentication.payload_extractor.origin_token.header', OriginTokenExtractor::class)
        ->abstract()
        ->factory([ExtractorFactory::class, 'fromOriginTokenHeader'])
            ->arg(0, abstract_arg('issuer'))
            ->arg(1, abstract_arg('header name'))

        ->set('istio.jwt_authentication.payload_extractor.origin_token.query_param', OriginTokenExtractor::class)
        ->abstract()
        ->factory([ExtractorFactory::class, 'fromOriginTokenQueryParam'])
            ->arg(0, abstract_arg('issuer'))
            ->arg(1, abstract_arg('param name'))

        ->set('istio.jwt_authentication.payload_extractor.base64_header', Base64HeaderExtractor::class)
        ->abstract()
        ->factory([ExtractorFactory::class, 'fromBase64Header'])
            ->arg(0, abstract_arg('issuer'))
            ->arg(1, abstract_arg('header name'))
    ;
};
