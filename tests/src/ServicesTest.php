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

use Derafu\TwigComponents\Abstract\AbstractComponent;
use Derafu\TwigComponents\Translation\TwigTranslationResourceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

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
    public function everyComponentIsTaggedForTheTwigEnvironment(): void
    {
        $tagged = array_keys($this->load()->findTaggedServiceIds('derafu_twig.component'));

        $this->assertCount(37, $tagged);
        foreach ($tagged as $class) {
            $this->assertTrue(is_subclass_of($class, AbstractComponent::class), $class);
            $this->assertNotEmpty((new ReflectionClass($class))->getAttributes(AsTwigComponent::class), $class);
        }
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
