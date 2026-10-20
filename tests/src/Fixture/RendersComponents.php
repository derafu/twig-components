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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Twig\Environment;

/**
 * Renders templates in the Symfony application of the tests.
 */
abstract class RendersComponents extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        // FrameworkBundle sets this variable when the kernel boots. It is set
        // before the first test so it is not a change that a test makes.
        $_ENV['DOCTRINE_DEPRECATIONS'] = $_SERVER['DOCTRINE_DEPRECATIONS'] ??= 'trigger';
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * Renders a template that is given as a text, in the given locale.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = [], string $locale = 'es'): string
    {
        // Without debug the kernel does not change the environment variables.
        self::bootKernel(['debug' => false]);

        $translator = self::getContainer()->get('translator');
        $this->assertInstanceOf(LocaleAwareInterface::class, $translator);
        $translator->setLocale($locale);

        $twig = self::getContainer()->get('twig');
        $this->assertInstanceOf(Environment::class, $twig);

        return $twig->createTemplate($template)->render($data);
    }
}
