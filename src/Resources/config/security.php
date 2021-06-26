<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Istio\Symfony\JWTAuthentication\Authenticator\Authenticator;
use Istio\Symfony\JWTAuthentication\User\StatelessUserProvider;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('istio.jwt_authentication.authenticator', Authenticator::class)
        ->abstract()
            ->arg(0, abstract_arg('user identifier claim mappings'))
            ->arg(1, abstract_arg('user provider'))
            ->arg(2, service(HttpMessageFactoryInterface::class))

        ->set('istio.jwt_authentication.stateless_user_provider', StatelessUserProvider::class)
        ->abstract()
            ->arg(0, abstract_arg('user classname'))
    ;
};
