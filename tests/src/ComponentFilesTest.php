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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Every component has everything that it needs: its name, its template, its
 * style, its example and a case in the tests.
 */
#[CoversClass(AbstractComponent::class)]
final class ComponentFilesTest extends TestCase
{
    /**
     * @return array<string, array{class-string, string}>
     */
    public static function provideComponents(): array
    {
        $components = [];
        foreach (glob(dirname(__DIR__, 2) . '/src/Component/Block/*Component.php') ?: [] as $file) {
            /** @var class-string $class */
            $class = 'Derafu\\TwigComponents\\Component\\Block\\' . basename($file, '.php');
            $attributes = (new ReflectionClass($class))->getAttributes(AsTwigComponent::class);
            $name = (string) ($attributes[0]->getArguments()[0] ?? '');
            $components[$name] = [$class, $name];
        }

        return $components;
    }

    /**
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('provideComponents')]
    public function theComponentHasItsFiles(string $class, string $name): void
    {
        $root = dirname(__DIR__, 2);
        /** @var array<string, mixed> $cases */
        $cases = json_decode((string) file_get_contents($root . '/tests/fixtures/components.json'), true, flags: JSON_THROW_ON_ERROR);

        $this->assertStringStartsWith('block-', $name);
        $this->assertFileExists($root . '/resources/templates/components/' . $name . '.html.twig');
        $this->assertFileExists($root . '/resources/css/components/' . $name . '.css');
        $this->assertFileExists($root . '/tests/fixtures/examples/' . $name . '.html.twig');
        $this->assertArrayHasKey(substr($name, strlen('block-')), $cases);
        $this->assertStringContainsString(
            '@import url("components/' . $name . '.css");',
            (string) file_get_contents($root . '/resources/css/components.css')
        );
    }

    #[Test]
    public function everyTemplateBelongsToAComponent(): void
    {
        $names = array_keys(self::provideComponents());
        $templates = array_map(
            fn (string $file) => basename($file, '.html.twig'),
            glob(dirname(__DIR__, 2) . '/resources/templates/components/*.html.twig') ?: []
        );
        sort($templates);
        sort($names);

        $this->assertSame($names, $templates);
    }
}
