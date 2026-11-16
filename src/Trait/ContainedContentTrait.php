<?php

declare(strict_types=1);

/**
 * Derafu: Twig - UI Component and Extension Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TwigComponents\Trait;

/**
 * Optional Bootstrap container inside a component's full-width root.
 */
trait ContainedContentTrait
{
    private bool $contained = false;

    public function getContained(): bool
    {
        return $this->contained;
    }

    public function setContained(bool $contained): static
    {
        $this->contained = $contained;

        return $this;
    }
}
