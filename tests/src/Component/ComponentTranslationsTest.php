<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents\Component;

use Derafu\TestsTwigComponents\Fixture\RendersComponents;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * The texts that the components write are in English, and they are translated by
 * the translator of the application (Symfony) when its locale has them.
 */
#[CoversNamespace('Derafu\TwigComponents')]
final class ComponentTranslationsTest extends RendersComponents
{
    /**
     * @return array<string, array{string, array<string, mixed>, string, string}>
     */
    public static function provideTexts(): array
    {
        $testimonials = ['testimonials' => [['background' => '/img/bg.jpg', 'content' => 'Great', 'author' => 'Maria']]];

        return [
            'a label of a button' => [
                '<twig:block-alert content="Test" :dismissible="true" />',
                [],
                'aria-label="Close"',
                'aria-label="Cerrar"',
            ],
            'a label with a value' => [
                '<twig:block-testimonials :testimonials="testimonials" />',
                $testimonials,
                'aria-label="Slide 1"',
                'aria-label="Diapositiva 1"',
            ],
            'an alternative text with a value' => [
                '<twig:block-testimonials :testimonials="testimonials" />',
                $testimonials,
                'alt="Background 1"',
                'alt="Fondo 1"',
            ],
            'a text that is only for readers of screens' => [
                '<twig:block-testimonials :testimonials="testimonials" />',
                $testimonials,
                '<span class="visually-hidden">Previous</span>',
                '<span class="visually-hidden">Anterior</span>',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[Test]
    #[DataProvider('provideTexts')]
    public function theTextIsInEnglishAndItIsTranslated(string $twig, array $data, string $english, string $spanish): void
    {
        $inEnglish = $this->render($twig, $data, 'en');
        $inSpanish = $this->render($twig, $data, 'es');

        $this->assertStringContainsString($english, $inEnglish);
        $this->assertStringContainsString($spanish, $inSpanish);
        $this->assertStringNotContainsString($english, $inSpanish);
    }
}
