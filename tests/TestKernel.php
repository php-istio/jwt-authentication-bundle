<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests;

use Istio\Symfony\JWTAuthentication\JWTAuthenticationBundle;
use Istio\Symfony\JWTAuthentication\Tests\Fixtures\SecureController;
use Istio\Symfony\JWTAuthentication\Tests\Fixtures\StatelessUser;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class TestKernel extends Kernel implements CompilerPassInterface
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new JWTAuthenticationBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(
            function (ContainerBuilder $container) {
                $this->registerFixtures($container);

                $container->loadFromExtension(
                    'framework',
                    [
                        'secret' => '',
                        'test' => true,
                        'router' => [
                            'resource' => __DIR__ . '/Fixtures/routes.php',
                            'type' => 'php',
                            'utf8' => true,
                        ],
                    ]
                );

                $container->loadFromExtension(
                    'security',
                    [
                        'enable_authenticator_manager' => true,
                        'access_control' => [
                            ['path' => '^/secure', 'roles' => [AuthenticatedVoter::IS_AUTHENTICATED_FULLY]],
                        ],
                        'firewalls' => [
                            'test' => [
                                'pattern' => '^/secure',
                                'provider' => 'istio',
                                'stateless' => true,
                                'istio_jwt_authenticator' => [
                                    'rules' => [
                                        [
                                            'issuer' => 'issuer_1',
                                            'user_identifier_claim' => 'id_1',
                                            'origin_token_query_params' => ['token'],
                                        ],
                                        [
                                            'issuer' => 'issuer_2',
                                            'user_identifier_claim' => 'id_2',
                                            'base64_headers' => ['x-istio-jwt-payload'],
                                        ],
                                    ],
                                ],
                            ],
                            'test2' => [
                                'provider' => 'memory',
                                'stateless' => true,
                                'istio_jwt_authenticator' => [
                                    'rules' => [
                                        [
                                            'issuer' => 'issuer_2',
                                            'user_identifier_claim' => 'id_2',
                                            'origin_token_headers' => ['authorization'],
                                            'prefix' => 'Bearer ',
                                        ],
                                    ],
                                ],
                            ],
                            // Test not affect another authenticator
                            'test3' => [
                                'provider' => 'istio',
                                'stateless' => true,
                                'http_basic' => [
                                    'realm' => 'Test',
                                ],
                            ],
                        ],
                        'providers' => [
                            'istio' => [
                                'istio_jwt_stateless' => [
                                    'class' => StatelessUser::class,
                                ],
                            ],
                            'memory' => [
                                'memory' => [
                                    'users' => [
                                        'valid' => [],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return sprintf('%s/tests/.kernel/cache', $this->getProjectDir());
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return sprintf('%s/tests/.kernel/logs', $this->getProjectDir());
    }

    private function publicServices(ContainerBuilder $container)
    {
        $container->getDefinition('security.authenticator.manager.test')->setPublic(true);
        $container->getDefinition('security.authenticator.manager.test2')->setPublic(true);
    }

    public function process(ContainerBuilder $container)
    {
        $this->publicServices($container);
        $this->registerFixtures($container);
    }

    private function registerFixtures(ContainerBuilder $container)
    {
        $container
            ->register('secure_controller', SecureController::class)
            ->setPublic(true)
            ->addTag('controller.service_arguments');

        $container->register(Psr17Factory::class, Psr17Factory::class);

        $psr17Factory = new Reference(Psr17Factory::class);
        $container
            ->register(HttpMessageFactoryInterface::class, PsrHttpFactory::class)
            ->setArguments([$psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory]);
    }
}
