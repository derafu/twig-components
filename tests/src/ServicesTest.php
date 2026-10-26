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

use Derafu\TestsTwigComponents\Fixture\RendersComponents;
use Derafu\Translation\TranslatorFactory;
use Derafu\TwigComponents\Abstract\AbstractComponent;
use Derafu\TwigComponents\Component\Block\AccordionComponent;
use Derafu\TwigComponents\Exception\TwigComponentException;
use Derafu\TwigComponents\Translation\TwigTranslationResourceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\ComponentFactory;
use Twig\Environment;

/**
 * The wiring that the package ships for `symfony/dependency-injection`.
 */
#[CoversClass(TwigTranslationResourceProvider::class)]
#[UsesClass(AbstractComponent::class)]
#[UsesClass(TwigComponentException::class)]
#[UsesNamespace('Derafu\TwigComponents\Component')]
final class ServicesTest extends RendersComponents
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

    #[Test]
    public function everyYamlComponentIsConstructedByTheRealUxFactory(): void
    {
        self::bootKernel(['environment' => 'services', 'debug' => false]);
        $container = self::getContainer();
        $factory = $container->get('ux.twig_component.component_factory');
        $this->assertInstanceOf(ComponentFactory::class, $factory);
        $twig = $container->get('twig');
        $this->assertInstanceOf(Environment::class, $twig);
        $this->assertTrue($twig->isStrictVariables());
        $tagged = $this->load()->findTaggedServiceIds('derafu_twig.component');
        $this->assertCount(37, $tagged);
        foreach (array_keys($tagged) as $class) {
            $metadata = $factory->metadataFor($class);
            $this->assertSame($class, $metadata->getServiceId());
            $component = $factory->get($metadata->getName());
            $this->assertInstanceOf(AbstractComponent::class, $component);
            $this->assertSame($class, $component::class);
            $this->assertTrue($twig->getLoader()->exists($metadata->getTemplate()));
            $this->assertNotSame($component, $factory->get($metadata->getName()), 'Each component must have its own state.');
        }
        $html = $twig->createTemplate('<twig:block-alert content="Configured component" :dismissible="true" />')->render();
        $this->assertStringContainsString('Configured component', $html);
        $this->assertStringContainsString('aria-label="Cerrar"', $html);
    }

    #[Test]
    public function taggedCataloguesTranslateLabelsAndRealComponentExceptions(): void
    {
        $container = $this->load();
        $container->register('app.translator', Translator::class)
            ->setFactory([TranslatorFactory::class, 'create'])
            ->setArguments(['es', ['en'], new TaggedIteratorArgument('derafu_translation.resource_provider')])
            ->setPublic(true);
        $container->getDefinition(AccordionComponent::class)->setPublic(true);
        $container->compile();
        $translator = $container->get('app.translator');
        $this->assertInstanceOf(Translator::class, $translator);
        $this->assertSame('Cerrar', $translator->trans('Close', [], 'twig'));
        $component = $container->get(AccordionComponent::class);
        $this->assertInstanceOf(AccordionComponent::class, $component);
        try {
            $component->setItems([['content' => 'Content']]);
            $this->fail('An accordion item without a title was accepted.');
        } catch (TwigComponentException $exception) {
            $this->assertSame(
                'Componente block-accordion: El título del ítem es obligatorio.',
                $exception->trans($translator)
            );
        }
    }
}
