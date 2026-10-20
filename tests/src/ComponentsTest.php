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
use Derafu\TwigComponents\Abstract\AbstractComponent;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Every component renders in a Symfony application with the strict mode of Twig,
 * with the least data that it needs.
 */
#[CoversNamespace('Derafu\TwigComponents')]
final class ComponentsTest extends RendersComponents
{
    /**
     * @return array<string, array{string, array<string, mixed>, string}>
     */
    public static function provideComponents(): array
    {
        /** @var array<string, array{string, array<string, mixed>, string}> $cases */
        $cases = json_decode(
            (string) file_get_contents(__DIR__ . '/../fixtures/components.json'),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        return $cases;
    }

    /**
     * @param array<string, mixed> $data
     */
    #[Test]
    #[DataProvider('provideComponents')]
    public function theComponentRendersWithTheLeastData(string $twig, array $data, string $expectedCssClass): void
    {
        $html = $this->render($twig, $data);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString($expectedCssClass, $html);
    }

    #[Test]
    public function theClassOfEveryComponentExtendsTheBaseOne(): void
    {
        $classes = glob(dirname(__DIR__, 2) . '/src/Component/Block/*Component.php') ?: [];

        $this->assertCount(37, $classes);
        foreach ($classes as $file) {
            $class = 'Derafu\\TwigComponents\\Component\\Block\\' . basename($file, '.php');
            $this->assertTrue(is_subclass_of($class, AbstractComponent::class), $class);
        }
    }
}
