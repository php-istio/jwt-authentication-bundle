<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication;

use Istio\Symfony\JWTAuthentication\DependencyInjection\Security\AuthenticatorFactory;
use Istio\Symfony\JWTAuthentication\DependencyInjection\Security\StatelessUserProviderFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class JWTAuthenticationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addUserProviderFactory(new StatelessUserProviderFactory());
        $extension->addAuthenticatorFactory(new AuthenticatorFactory());
    }
}
