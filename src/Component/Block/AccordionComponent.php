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
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('block-accordion')]
final class AccordionComponent extends AbstractComponent
{
    /**
     * Array of accordion items.
     *
     * Each item should contain:
     *
     *   - `title`.
     *   - `content`.
     *   - `active`.
     *
     * @var array<int,array<string,mixed>>
     */
    private array $items = [];

    /**
     * Gets the accordion items.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Sets the accordion items.
     *
     * @param array<int,array<string,mixed>> $items The accordion items.
     * @return static
     */
    public function setItems(array $items): static
    {
        foreach ($items as &$item) {
            if (empty($item['title'])) {
                $this->error('Item title is required.');
            }

            if (empty($item['content'])) {
                $this->error('Item content is required.');
            }

            $item['active'] = $item['active'] ?? false;
        }

        $this->items = $items;

        return $this;
    }
}
