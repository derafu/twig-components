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

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\UX\TwigComponent\TwigComponentBundle;

/**
 * The application of the tests: Symfony with the components configured as the
 * README says, and Twig in its strict mode (the default of Symfony in `dev`).
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new TwigComponentBundle();
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__, 3);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/kernel/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'tests',
            'test' => true,
            'default_locale' => 'es',
            'translator' => [
                'paths' => ['%kernel.project_dir%/resources/translations'],
                // The texts are in English, which is the id of their translations.
                'fallbacks' => ['en'],
            ],
        ]);

        $container->extension('twig', [
            'strict_variables' => true,
            'paths' => ['%kernel.project_dir%/resources/templates' => null],
        ]);

        $container->import($this->getProjectDir() . '/resources/config/twig-components-services.yaml');
    }
}
