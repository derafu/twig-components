<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents\Fixture;

/**
 * An HTML page that opens in a browser, to look at a rendered component:
 * Bootstrap and Font Awesome come from a CDN and the CSS is the one of the
 * package, linked by its path (so a change in it shows by reloading the page).
 * The page is meant to be opened from the file system, from
 * `tests/fixtures/examples/`, next to its `assets/`.
 */
final class StandalonePage
{
    private const BOOTSTRAP = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist';

    private const COMPONENTS = '../../../resources/css/components.css';

    private const FONT_AWESOME = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css';

    /**
     * @param string $title Title of the page.
     * @param string $body The HTML of the rendered component.
     */
    public static function wrap(string $title, string $body): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES);
        $bootstrap = self::BOOTSTRAP;
        $fontAwesome = self::FONT_AWESOME;
        $components = self::COMPONENTS;

        return <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>{$title}</title>
                <link rel="stylesheet" href="{$bootstrap}/css/bootstrap.min.css">
                <link rel="stylesheet" href="{$fontAwesome}">
                <link rel="stylesheet" href="{$components}">
            </head>
            <body>
            {$body}
                <script src="{$bootstrap}/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>

            HTML;
    }
}
