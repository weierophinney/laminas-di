<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Container;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Di\CodeGenerator\InjectorGenerator;
use Zend\Di\Config;
use Zend\Di\ConfigInterface;
use Zend\Di\Container\GeneratorFactory;
use Zend\Di\Injector;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Zend\Di\Container\GeneratorFactory
 *
 */
class GeneratorFactoryTest extends TestCase
{
    public function testInvokeCreatesGenerator()
    {
        $injector = new Injector();
        $factory = new GeneratorFactory();

        $result = $factory->create($injector->getContainer());
        $this->assertInstanceOf(InjectorGenerator::class, $result);
    }

    public function testFactoryUsesDiConfigContainer(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('has')->willReturnCallback(function ($type) {
            return ($type == ConfigInterface::class);
        });

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn(new Config());

        $factory = new GeneratorFactory();
        $factory->create($container);
    }

    public function testSetsOutputDirectoryFromConfig()
    {
        $vfs = vfsStream::setup(uniqid('zend-di'));
        $expected = $vfs->url();
        $container = new ServiceManager();
        $container->setService('config', [
            'dependencies' => [
                'auto' => [
                    'aot' => [
                        'directory' => $expected
                    ],
                ],
            ],
        ]);

        $generator = (new GeneratorFactory())->create($container);
        $this->assertEquals($expected, $generator->getOutputDirectory());
    }

    public function testSetsNamespaceFromConfig()
    {
        $expected = 'ZendTest\\Di\\' . uniqid('Generated');
        $container = new ServiceManager();
        $container->setService('config', [
            'dependencies' => [
                'auto' => [
                    'aot' => [
                        'namespace' => $expected,
                    ],
                ],
            ],
        ]);

        $generator = (new GeneratorFactory())->create($container);
        $this->assertEquals($expected, $generator->getNamespace());
    }
}
