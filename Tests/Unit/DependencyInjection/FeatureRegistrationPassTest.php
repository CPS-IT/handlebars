<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\Tests\Unit\DependencyInjection;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\Config;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * FeatureRegistrationPassTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\FeatureRegistrationPass::class)]
final class FeatureRegistrationPassTest extends TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var array<string, bool>
     */
    private array $activatedFeatures = [
        'blockHelper' => false,
        'contentHelper' => false,
        'extendHelper' => false,
        'renderHelper' => false,
        'flatTemplateResolver' => false,
    ];

    #[Framework\Attributes\Test]
    public function processDoesNotActivateDisabledFeatures(): void
    {
        $container = $this->buildContainer();

        self::assertSame([], $container->findTaggedServiceIds('handlebars.helper'));
        self::assertNotInstanceOf(Src\Renderer\Template\FlatTemplateResolver::class, $container->get('handlebars.template_resolver'));
        self::assertNotInstanceOf(Src\Renderer\Template\FlatTemplateResolver::class, $container->get('handlebars.partial_resolver'));
    }

    #[Framework\Attributes\Test]
    public function processActivatesEnabledHelpers(): void
    {
        $this->activatedFeatures['blockHelper'] = true;
        $this->activatedFeatures['contentHelper'] = true;
        $this->activatedFeatures['extendHelper'] = true;
        $this->activatedFeatures['renderHelper'] = true;

        $container = $this->buildContainer();

        self::assertCount(4, $container->findTaggedServiceIds('handlebars.helper'));
        self::assertHelperIsTagged($container, Src\Renderer\Helper\BlockHelper::class, 'block');
        self::assertHelperIsTagged($container, Src\Renderer\Helper\ContentHelper::class, 'content');
        self::assertHelperIsTagged($container, Src\Renderer\Helper\ExtendHelper::class, 'extend');
        self::assertHelperIsTagged($container, Src\Renderer\Helper\RenderHelper::class, 'render');
    }

    #[Framework\Attributes\Test]
    public function processActivatesEnabledTemplateResolvers(): void
    {
        $this->activatedFeatures['flatTemplateResolver'] = true;

        $container = $this->buildContainer();

        self::assertSame([], $container->findTaggedServiceIds('handlebars.helper'));
        self::assertInstanceOf(Src\Renderer\Template\FlatTemplateResolver::class, $container->get('handlebars.template_resolver'));
        self::assertInstanceOf(Src\Renderer\Template\FlatTemplateResolver::class, $container->get('handlebars.partial_resolver'));
    }

    #[Framework\Attributes\Test]
    public function processDoesNotActivateFeaturesIfExtensionConfigurationIsMissing(): void
    {
        unset($this->activatedFeatures['blockHelper']);
        unset($this->activatedFeatures['contentHelper']);
        unset($this->activatedFeatures['extendHelper']);
        unset($this->activatedFeatures['renderHelper']);
        unset($this->activatedFeatures['flatTemplateResolver']);

        $container = $this->buildContainer();

        self::assertSame([], $container->findTaggedServiceIds('handlebars.helper'));
        self::assertNotInstanceOf(Src\Renderer\Template\FlatTemplateResolver::class, $container->get('handlebars.template_resolver'));
        self::assertNotInstanceOf(Src\Renderer\Template\FlatTemplateResolver::class, $container->get('handlebars.partial_resolver'));
    }

    /**
     * @param class-string<Src\Renderer\Helper\HelperInterface> $className
     */
    private static function assertHelperIsTagged(DependencyInjection\ContainerBuilder $container, string $className, string $name): void
    {
        $serviceIds = $container->findTaggedServiceIds('handlebars.helper');
        $expectedConfiguration = [
            'identifier' => $name,
            'method' => 'evaluate',
        ];

        self::assertArrayHasKey($className, $serviceIds);
        self::assertSame($expectedConfiguration, $serviceIds[$className][0]);
    }

    private function buildContainer(): DependencyInjection\ContainerBuilder
    {
        $container = new DependencyInjection\ContainerBuilder();
        $container->registerExtension(new Src\DependencyInjection\Extension\HandlebarsExtension());

        $yamlFileLoader = new DependencyInjection\Loader\YamlFileLoader($container, new Config\FileLocator(\dirname(__DIR__) . '/Fixtures/Services'));
        $yamlFileLoader->load('feature-registration-services.yaml');

        // Constructor arguments of helpers
        $container->register(Core\TypoScript\TypoScriptService::class);
        $container->register(Frontend\ContentObject\ContentObjectRenderer::class);
        $container->register(Log\LoggerInterface::class, Log\NullLogger::class);

        // Provide dummy extension configuration class
        $dummyExtensionConfiguration = new Tests\Unit\Fixtures\Classes\DummyExtensionConfiguration($this->activatedFeatures);
        $container->set(Core\Configuration\ExtensionConfiguration::class, $dummyExtensionConfiguration);

        // Simulate required services
        $dummyTemplatePathsDefinition = new DependencyInjection\Definition(Src\Renderer\Template\TemplatePaths::class);
        $dummyTemplatePathsDefinition->addArgument(new Tests\Unit\Fixtures\Classes\DummyConfigurationManager());
        $dummyTemplateResolverDefinition = (new DependencyInjection\Definition('stdClass'))->setPublic(true);
        $dummyTemplateResolverDefinition->addArgument(new DependencyInjection\Reference(Src\Renderer\Template\TemplatePaths::class));

        $container->setDefinition(Src\Renderer\Template\TemplatePaths::class, $dummyTemplatePathsDefinition);
        $container->setDefinition('handlebars.template_resolver', $dummyTemplateResolverDefinition);
        $container->setDefinition('handlebars.partial_resolver', $dummyTemplateResolverDefinition);
        $container->setDefinition(Src\Renderer\RendererInterface::class, $dummyTemplateResolverDefinition);

        $container->setParameter(Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_TEMPLATE_ROOT_PATHS, []);
        $container->setParameter(Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_PARTIAL_ROOT_PATHS, []);

        $container->addCompilerPass(new Src\DependencyInjection\FeatureRegistrationPass());
        $container->addCompilerPass(new Core\DependencyInjection\PublicServicePass('handlebars.helper'));
        $container->compile();

        return $container;
    }
}
