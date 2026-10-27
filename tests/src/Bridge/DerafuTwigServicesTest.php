<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents\Bridge;

use Derafu\Twig\Contract\TwigServiceInterface;
use Derafu\TwigComponents\Bridge\DerafuTwig\TwigComponentsTemplatePathProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The optional bridge contributes to Twig's real shared service graph.
 * Native Symfony integration is exercised separately by the existing suite.
 */
#[CoversClass(TwigComponentsTemplatePathProvider::class)]
#[UsesNamespace('Derafu\TwigComponents')]
final class DerafuTwigServicesTest extends TestCase
{
    private function container(string $fixture = 'derafu-twig.yaml'): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/fixtures/config')))
            ->load($fixture);
        $container->getDefinition(TwigServiceInterface::class)->setPublic(true);
        $container->compile();

        return $container;
    }

    private function twig(ContainerInterface $container): TwigServiceInterface
    {
        $twig = $container->get(TwigServiceInterface::class);
        self::assertInstanceOf(TwigServiceInterface::class, $twig);

        return $twig;
    }

    public function testTheOptionalYamlRendersRealComponentsWithTheTaggedTranslations(): void
    {
        $twig = $this->twig($this->container());
        $data = [];
        $html = $twig->renderFromString(
            '<twig:block-alert content="Configured component" :dismissible="true" />',
            $data
        );

        self::assertStringContainsString('Configured component', $html);
        self::assertStringContainsString('derafu-block-alert', $html);
        self::assertStringContainsString('aria-label="Cerrar"', $html);
    }

    public function testApplicationPathsOverrideOneTemplateAndKeepTheOtherPackageTemplates(): void
    {
        $twig = $this->twig($this->container('derafu-twig-application.yaml'));
        $data = [];
        $html = $twig->renderFromString(
            '<twig:block-alert content="Application template" /><twig:block-title title="Package template" />',
            $data
        );

        self::assertStringContainsString('<aside data-application-alert>Application template</aside>', $html);
        self::assertStringContainsString('derafu-block-title', $html);
        self::assertStringContainsString('Package template', $html);
        self::assertStringNotContainsString('derafu-block-alert', $html);
    }

    public function testTheCompiledContainerUsesTheOptionalBridge(): void
    {
        $container = $this->container('derafu-twig-application.yaml');
        $class = 'TwigComponentsDerafuTwigContainer';
        $code = '';
        if (!class_exists($class, false)) {
            $dump = (new PhpDumper($container))->dump(['class' => $class]);
            self::assertIsString($dump);
            $code = substr($dump, 5);
        }
        $compiled = eval($code . "\nreturn new " . $class . '();');
        self::assertInstanceOf(ContainerInterface::class, $compiled);
        $twig = $this->twig($compiled);
        $data = [];
        $html = $twig->renderFromString(
            '<twig:block-alert content="Compiled application" /><twig:block-title title="Compiled package" />',
            $data
        );

        self::assertStringContainsString('<aside data-application-alert>Compiled application</aside>', $html);
        self::assertStringContainsString('derafu-block-title', $html);
        self::assertStringContainsString('Compiled package', $html);
    }
}
