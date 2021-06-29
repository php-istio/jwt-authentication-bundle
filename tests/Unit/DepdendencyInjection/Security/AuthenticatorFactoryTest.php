<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Unit\DepdendencyInjection\Security;

use Istio\Symfony\JWTAuthentication\DependencyInjection\Security\AuthenticatorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AuthenticatorFactoryTest extends TestCase
{
    use ContainerSetupTrait;

    /**
     * @dataProvider validConfigurations
     */
    public function testAddValidConfiguration(array $inputConfig, array $expectedConfig)
    {
        $factory = new AuthenticatorFactory();
        $nodeDefinition = new ArrayNodeDefinition('istio_jwt_authenticator');
        $factory->addConfiguration($nodeDefinition);

        $node = $nodeDefinition->getNode();
        $normalizedConfig = $node->normalize($inputConfig);
        $finalizedConfig = $node->finalize($normalizedConfig);

        $this->assertSame($expectedConfig, $finalizedConfig);
    }

    /**
     * @dataProvider invalidConfigurations
     */
    public function testAddInvalidConfiguration(array $inputConfig)
    {
        $this->expectException(InvalidConfigurationException::class);

        $factory = new AuthenticatorFactory();
        $nodeDefinition = new ArrayNodeDefinition('istio_jwt_authenticator');
        $factory->addConfiguration($nodeDefinition);

        $node = $nodeDefinition->getNode();
        $normalizedConfig = $node->normalize($inputConfig);

        // will validate and throw an exception on invalid
        $node->finalize($normalizedConfig);
    }

    public function testExceptionWhenCallCreate()
    {
        $this->expectException(\LogicException::class);

        $factory = new AuthenticatorFactory();
        $factory->create(new ContainerBuilder(), 'test', [], 'test', 'test');
    }

    public function testCreateAuthenticator()
    {
        $config = [
            'rules' => [
                ['issuer' => 'test', 'origin_token_headers' => ['authorization'], 'user_identifier_claim' => 'sub'],
                ['issuer' => 'test2', 'origin_token_query_params' => ['token'], 'user_identifier_claim' => 'sub'],
            ],
        ];

        $this->executeCreate($config);

        $definition = $this->container->getDefinition('security.authenticator.istio_jwt_authenticator.test');

        $this->assertInstanceOf(IteratorArgument::class, $definition->getArgument(0));
        $this->assertInstanceOf(Reference::class, $definition->getArgument(1));
        $this->assertSame(2, count($definition->getArgument(0)->getValues()));
        $this->assertSame('test', (string) $definition->getArgument(1));
    }

    public function testThrowExceptionWhenCreateAuthenticatorWithNoneExtractor()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->executeCreate(['rules' => ['issuer' => 'test']]);
    }

    private function executeCreate(array $config)
    {
        $factory = new AuthenticatorFactory();
        $factory->createAuthenticator($this->container, 'test', $config, 'test');
    }

    public function validConfigurations(): array
    {
        return [
            [
                [
                    'rules' => [
                        [
                            'issuer' => 'example',
                        ],
                    ],
                ],
                [
                    'rules' => [
                        [
                            'issuer' => 'example',
                            'user_identifier_claim' => 'sub',
                            'origin_token_headers' => [],
                            'origin_token_query_params' => [],
                            'base64_headers' => [],
                        ],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        [
                            'issuer' => 'example',
                            'user_identifier_claim' => 'id',
                            'origin_token_headers' => ['authorization'],
                            'origin_token_query_params' => ['token'],
                            'base64_headers' => ['x-istio-jwt-payload'],
                        ],
                    ],
                ],
                [
                    'rules' => [
                        [
                            'issuer' => 'example',
                            'user_identifier_claim' => 'id',
                            'origin_token_headers' => ['authorization'],
                            'origin_token_query_params' => ['token'],
                            'base64_headers' => ['x-istio-jwt-payload'],
                        ],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        [
                            'issuer' => 'example',
                            'user_identifier_claim' => 'id',
                            'origin_token_header' => ['authorization'],
                            'origin_token_query_param' => ['token'],
                            'base64_header' => ['x-istio-jwt-payload'],
                        ],
                    ],
                ],
                [
                    'rules' => [
                        [
                            'issuer' => 'example',
                            'user_identifier_claim' => 'id',
                            'origin_token_headers' => ['authorization'],
                            'origin_token_query_params' => ['token'],
                            'base64_headers' => ['x-istio-jwt-payload'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidConfigurations(): array
    {
        return [
            [
                [],
            ],
            [
                [
                    'rules' => [],
                ],
            ],
            [
                [
                    'rules' => [[]],
                ],
            ],
            [
                [
                    'rules' => ['issuer' => ''],
                ],
            ],
            [
                [
                    'rules' => [
                        ['issuer' => ''],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        ['issuer' => 'example', 'user_identifier_claim' => ''],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        ['issuer' => '', 'user_identifier_claim' => 'id'],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        ['issuer' => 'example', 'origin_token_headers' => ['']],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        ['issuer' => 'example', 'origin_token_query_params' => ['']],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        ['issuer' => 'example', 'base64_headers' => ['']],
                    ],
                ],
            ],
        ];
    }
}
