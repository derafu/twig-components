<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents;

use Derafu\TwigComponents\Translation\TwigTranslationResourceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The wiring that the package ships for `symfony/dependency-injection`.
 */
#[CoversClass(TwigTranslationResourceProvider::class)]
final class ServicesTest extends TestCase
{
    private function load(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/resources/config')))
            ->load('twig-components-services.yaml');

        return $container;
    }

    #[Test]
    public function theTranslationsOfThePackageAreTaggedForTheTranslator(): void
    {
        $definition = $this->load()->getDefinition(TwigTranslationResourceProvider::class);

        $this->assertArrayHasKey('derafu_translation.resource_provider', $definition->getTags());
    }

    #[Test]
    public function theProviderOfTheContainerGivesTheDirectoryOfTheCatalogues(): void
    {
        $container = $this->load();
        $container->getDefinition(TwigTranslationResourceProvider::class)->setPublic(true);
        $container->compile();

        $provider = $container->get(TwigTranslationResourceProvider::class);
        $this->assertInstanceOf(TwigTranslationResourceProvider::class, $provider);

        foreach ($provider->getDirectories() as $directory) {
            $this->assertFileExists($directory . '/twig+intl-icu.es.php');
            $this->assertFileExists($directory . '/errors+intl-icu.es.php');
        }
    }
}
