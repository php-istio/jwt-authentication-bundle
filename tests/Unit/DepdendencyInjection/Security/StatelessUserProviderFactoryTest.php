<?php
/*
 * (c) Minh Vuong <vuongxuongminh@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace Istio\Symfony\JWTAuthentication\Tests\Unit\DepdendencyInjection\Security;

use Istio\Symfony\JWTAuthentication\DependencyInjection\Security\StatelessUserProviderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class StatelessUserProviderFactoryTest extends TestCase
{
    use ContainerSetupTrait;

    /**
     * @dataProvider validConfigurations
     */
    public function testAddValidConfiguration(array $inputConfig, array $expectedConfig)
    {
        $factory = new StatelessUserProviderFactory();
        $nodeDefinition = new ArrayNodeDefinition('istio_jwt_stateless');
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

        $factory = new StatelessUserProviderFactory();
        $nodeDefinition = new ArrayNodeDefinition('istio_jwt_stateless');
        $factory->addConfiguration($nodeDefinition);

        $node = $nodeDefinition->getNode();
        $normalizedConfig = $node->normalize($inputConfig);

        // will validate and throw an exception on invalid
        $node->finalize($normalizedConfig);
    }

    public function validConfigurations(): array
    {
        return [
            [
                ['class' => 'example'],
                ['class' => 'example'],
            ],
        ];
    }

    public function invalidConfigurations(): array
    {
        return [
            [
                ['class' => ''],
                ['class' => 0],
                ['class' => []],
                [],
            ],
        ];
    }

    public function testCallCreate()
    {
        $this->executeCreate(['class' => 'test']);

        $definition = $this->container->getDefinition('test');
        $this->assertEquals('test', $definition->getArgument(0));
    }

    private function executeCreate(array $config)
    {
        $factory = new StatelessUserProviderFactory();
        $factory->create($this->container, 'test', $config);
    }
}
