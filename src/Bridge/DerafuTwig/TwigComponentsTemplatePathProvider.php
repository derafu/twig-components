<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TwigComponents\Bridge\DerafuTwig;

use Derafu\Twig\Contract\TemplatePathProviderInterface;

/**
 * Optional template-path contribution for applications using derafu/twig.
 */
final class TwigComponentsTemplatePathProvider implements TemplatePathProviderInterface
{
    public function getPaths(): array
    {
        return [dirname(__DIR__, 3) . '/resources/templates'];
    }
}
