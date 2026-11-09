<?php

declare(strict_types=1);

/**
 * Derafu: Twig - UI Component and Extension Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents\Component;

use Closure;
use Derafu\Translation\TranslatorFactory;
use Derafu\TwigComponents\Abstract\AbstractComponent;
use Derafu\TwigComponents\Component\Block\AccordionComponent;
use Derafu\TwigComponents\Component\Block\BoxesComponent;
use Derafu\TwigComponents\Component\Block\CardGridComponent;
use Derafu\TwigComponents\Component\Block\TeamComponent;
use Derafu\TwigComponents\Component\Block\TimelineComponent;
use Derafu\TwigComponents\Exception\TwigComponentException;
use Derafu\TwigComponents\Translation\TwigTranslationResourceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The error of a component names the component and says what is wrong, and both
 * are translated.
 */
#[CoversClass(AbstractComponent::class)]
#[CoversClass(AccordionComponent::class)]
#[CoversClass(BoxesComponent::class)]
#[CoversClass(CardGridComponent::class)]
#[CoversClass(TeamComponent::class)]
#[CoversClass(TimelineComponent::class)]
#[UsesClass(TwigComponentException::class)]
#[UsesClass(TwigTranslationResourceProvider::class)]
final class ComponentErrorsTest extends TestCase
{
    /**
     * @return array<string, array{callable(): mixed, string, string}>
     */
    public static function provideErrors(): array
    {
        return [
            'a message without parameters' => [
                fn () => (new AccordionComponent())->setItems([['content' => 'Content']]),
                'Component block-accordion: Item title is required.',
                'Componente block-accordion: El título del ítem es obligatorio.',
            ],
            'a required value of an item' => [
                fn () => (new AccordionComponent())->setItems([['title' => 'Title']]),
                'Component block-accordion: Item content is required.',
                'Componente block-accordion: El contenido del ítem es obligatorio.',
            ],
            'a number of columns that does not exist' => [
                fn () => (new BoxesComponent())->setCols(5),
                'Component block-boxes: The number of columns must be 1, 2, 3, 4 or 6.',
                'Componente block-boxes: La cantidad de columnas debe ser 1, 2, 3, 4 o 6.',
            ],
            'a number of columns that does not exist in a grid of cards' => [
                fn () => (new CardGridComponent())->setCols(5),
                'Component block-card-grid: Invalid number of columns. Must be 1, 2, 3, 4 or 6.',
                'Componente block-card-grid: Cantidad de columnas inválida. Debe ser 1, 2, 3, 4 o 6.',
            ],
            'a number of columns that does not exist in a team' => [
                fn () => (new TeamComponent())->setCols(5),
                'Component block-team: Invalid number of columns. Must be 1, 2, 3, 4, or 6.',
                'Componente block-team: Cantidad de columnas inválida. Debe ser 1, 2, 3, 4 o 6.',
            ],
            'a message with a parameter' => [
                fn () => (new TimelineComponent())->setEvents([['date' => 20240101]]),
                'Component block-timeline: Invalid date format for event: 20240101',
                'Componente block-timeline: Formato de fecha inválido para el evento: 20240101',
            ],
            'a message with a parameter that has no value' => [
                fn () => (new TimelineComponent())->setEvents([['date' => null]]),
                'Component block-timeline: Invalid date format for event: N/A',
                'Componente block-timeline: Formato de fecha inválido para el evento: N/A',
            ],
            'a message with a parameter that is an array' => [
                fn () => (new TimelineComponent())->setEvents([['date' => ['2024-01-01']]]),
                'Component block-timeline: Invalid date format for event: N/A',
                'Componente block-timeline: Formato de fecha inválido para el evento: N/A',
            ],
            'a message with a parameter that is an object' => [
                fn () => (new TimelineComponent())->setEvents([['date' => new stdClass()]]),
                'Component block-timeline: Invalid date format for event: N/A',
                'Componente block-timeline: Formato de fecha inválido para el evento: N/A',
            ],
        ];
    }

    /**
     * @param callable(): mixed $call
     */
    #[DataProvider('provideErrors')]
    public function testTheErrorIsThrownAndTranslated(callable $call, string $english, string $spanish): void
    {
        try {
            $call();
            $this->fail('The component did not throw its error.');
        } catch (TwigComponentException $exception) {
            $translator = TranslatorFactory::create('es', [], [new TwigTranslationResourceProvider()]);

            $this->assertSame($english, $exception->getMessage());
            $this->assertSame($spanish, $exception->trans($translator));
        }
    }

    /**
     * @return iterable<string, array{Closure(): AbstractComponent, string}>
     */
    public static function provideComponentsWithoutAValidName(): iterable
    {
        yield 'no attribute' => [
            static fn (): AbstractComponent => new class () extends AbstractComponent {
            },
            'missing attribute',
        ];
        yield 'name omitted' => [
            static fn (): AbstractComponent => new #[AsTwigComponent()] class () extends AbstractComponent {
            },
            'null',
        ];
        yield 'explicit null' => [
            static fn (): AbstractComponent => new #[AsTwigComponent(null)] class () extends AbstractComponent {
            },
            'null',
        ];
        yield 'empty string' => [
            static fn (): AbstractComponent => new #[AsTwigComponent('')] class () extends AbstractComponent {
            },
            'string',
        ];
    }

    /**
     * @param Closure(): AbstractComponent $create
     */
    #[DataProvider('provideComponentsWithoutAValidName')]
    public function testAComponentRequiresAnAttributeWithANonEmptyStringName(Closure $create, string $type): void
    {
        $component = $create();
        $translator = TranslatorFactory::create('es', [], [new TwigTranslationResourceProvider()]);

        try {
            $component->getId();
            $this->fail('A component must declare an attribute with a non-empty string name.');
        } catch (TwigComponentException $exception) {
            if ($type === 'missing attribute') {
                $this->assertSame(
                    'Twig component ' . $component::class . ' must declare the AsTwigComponent attribute.',
                    $exception->getMessage()
                );
                $this->assertSame(
                    'El componente Twig ' . $component::class . ' debe declarar el atributo AsTwigComponent.',
                    $exception->trans($translator)
                );
            } else {
                $this->assertSame(
                    'The name of Twig component ' . $component::class . ' must be a non-empty string; ' . $type . ' given.',
                    $exception->getMessage()
                );
                $this->assertSame(
                    'El nombre del componente Twig ' . $component::class . ' debe ser un string no vacío; se recibió ' . $type . '.',
                    $exception->trans($translator)
                );
            }
        }
    }
}
