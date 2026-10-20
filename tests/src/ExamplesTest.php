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
use Derafu\TestsTwigComponents\Fixture\StandalonePage;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * The examples of `tests/fixtures/examples/` (the ones of the documentation, with
 * all the data that each component can have) render in a Symfony application with
 * the strict mode of Twig. Each one is saved next to its template, as an HTML page
 * that opens in a browser.
 */
#[CoversNamespace('Derafu\TwigComponents')]
final class ExamplesTest extends RendersComponents
{
    /**
     * Variables that the page of the documentation gives to its examples.
     *
     * @var array<string, array<string, mixed>>
     */
    private const DATA = [
        'block-scrollspy' => ['section_content_introduction' => 'Introduction of the example.'],
    ];

    /**
     * @return array<string, array{string}>
     */
    public static function provideExamples(): array
    {
        $examples = [];
        foreach (glob(dirname(__DIR__, 2) . '/tests/fixtures/examples/*.html.twig') ?: [] as $file) {
            $name = basename($file, '.html.twig');
            $examples[$name] = [$name];
        }

        return $examples;
    }

    #[Test]
    #[DataProvider('provideExamples')]
    public function theExampleRenders(string $name): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2) . '/tests/fixtures/examples/' . $name . '.html.twig');
        // The front matter is data for the page of the documentation.
        $template = (string) preg_replace('/\A---\n.*?\n---\n/s', '', $source);

        $html = $this->render($template, self::DATA[$name] ?? []);

        // The page to look at the component in a browser.
        // It is not versioned (see the `.gitignore`).
        file_put_contents(
            dirname(__DIR__, 2) . '/tests/fixtures/examples/' . $name . '.html',
            StandalonePage::wrap($name, $html)
        );

        // The components were rendered: there is HTML, and no tag of a component
        // is left.
        $this->assertNotSame('', trim($html));
        $this->assertStringNotContainsString('<twig:', $html);
    }

    #[Test]
    public function theFilesThatTheExamplesUseExist(): void
    {
        $directory = dirname(__DIR__, 2) . '/tests/fixtures/examples';

        $used = [];
        foreach (glob($directory . '/*.html.twig') ?: [] as $file) {
            preg_match_all('#(?<![\w/.])assets/[\w./-]+#', (string) file_get_contents($file), $matches);
            array_push($used, ...$matches[0]);
        }

        $this->assertNotEmpty($used);
        foreach (array_unique($used) as $path) {
            $this->assertFileExists($directory . '/' . $path);
        }
        // The CSS that the pages link: the one of the package.
        $this->assertFileExists($directory . '/../../../resources/css/components.css');
    }

    #[Test]
    public function thereIsAnExampleOfEveryComponent(): void
    {
        $this->assertCount(37, self::provideExamples());
    }
}
