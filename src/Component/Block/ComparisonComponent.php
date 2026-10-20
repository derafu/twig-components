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

#[AsTwigComponent('block-comparison')]
class ComparisonComponent extends AbstractComponent
{
    /**
     * Array of plans with their configurations.
     *
     * Each plan contains:
     * - title: Plan title
     * - price: Plan price
     * - period: Billing period (/month, /year, etc)
     * - features: Array of features:
     *   - text: Feature text
     *   - value: Specific value or true/false
     *   - tooltip: Optional tooltip
     * - button_text: Button text
     * - button_url: Button URL
     * - highlighted: true/false to highlight the plan
     *
     * @var array<int,array<string,mixed>>
     */
    private array $plans = [];

    /**
     * Gets the array of plans.
     *
     * @return array<int,array<string,mixed>> The array of plans with their configurations.
     */
    public function getPlans(): array
    {
        return $this->plans;
    }

    /**
     * Sets the array of plans.
     *
     * @param array<int,array<string,mixed>> $plans The array of plans with their configurations.
     * @return static
     */
    public function setPlans(array $plans): static
    {
        $this->plans = $plans;

        return $this;
    }
}
