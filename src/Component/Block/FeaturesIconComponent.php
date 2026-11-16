<?php

declare(strict_types=1);

/**
 * Derafu: Twig - UI Component and Extension Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TwigComponents\Component\Block;

use Derafu\TwigComponents\Abstract\AbstractComponent;
use Derafu\TwigComponents\Trait\ContainedContentTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('block-features-icon')]
class FeaturesIconComponent extends AbstractComponent
{
    use ContainedContentTrait;

    /**
     * Explicit desktop columns, or null for automatic grouping.
     */
    private ?int $cols = null;

    /**
     * Content alignment: left, center or right; start and end are aliases.
     */
    private string $align = 'center';

    public function getCols(): ?int
    {
        return $this->cols;
    }

    public function setCols(?int $cols): static
    {
        if ($cols !== null && !in_array($cols, [1, 2, 3, 4, 6], true)) {
            $this->error('Invalid number of columns. Must be 1, 2, 3, 4 or 6.');
        }

        $this->cols = $cols;

        return $this;
    }

    public function getAlign(): string
    {
        return $this->align;
    }

    public function setAlign(string $align): static
    {
        $this->align = $align;

        return $this;
    }

    /**
     * Array of features with their configurations.
     *
     * Each feature contains:
     * - icon: Font Awesome icon class (e.g. "fa-solid fa-display")
     * - title: Feature title
     * - content: Feature content (supports HTML)
     *
     * @var array<int,array<string,mixed>>
     */
    private array $features = [];

    /**
     * Gets the array of features.
     *
     * @return array<int,array<string,mixed>> The array of features with their configurations.
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * Sets the array of features.
     *
     * @param array<int,array<string,mixed>> $features The array of features with their configurations.
     * @return static
     */
    public function setFeatures(array $features): static
    {
        $this->features = $features;

        return $this;
    }
}
