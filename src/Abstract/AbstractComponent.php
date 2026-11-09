<?php

declare(strict_types=1);

/**
 * Derafu: Twig - UI Component and Extension Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TwigComponents\Abstract;

use Derafu\Translation\TranslatableMessage;
use Derafu\TwigComponents\Exception\TwigComponentException;
use ReflectionClass;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Abstract base class for Twig components.
 *
 * This class provides common functionality for all Twig components.
 */
abstract class AbstractComponent
{
    /**
     * Unique identifier for the component instance.
     *
     * @var string
     */
    private string $id;

    /**
     * Gets the component's unique identifier.
     *
     * Automatically generated if not set.
     *
     * @return string
     */
    public function getId(): string
    {
        if (!isset($this->id)) {
            $this->id = uniqid($this->getComponentName() . '--');
        }

        return $this->id;
    }

    /**
     * Sets the component's unique identifier.
     *
     * @param string $id The component's unique identifier.
     * @return static For method chaining.
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the explicit non-empty name declared by the component attribute.
     *
     * Supports positional and named arguments of AsTwigComponent.
     *
     * @return non-empty-string
     * @throws TwigComponentException If the attribute is absent or its name is
     * not a non-empty string.
     */
    protected function getComponentName(): string
    {
        $attributes = (new ReflectionClass($this))->getAttributes(AsTwigComponent::class);
        if ($attributes === []) {
            throw new TwigComponentException([
                'Twig component {component} must declare the AsTwigComponent attribute.',
                'component' => static::class,
            ]);
        }

        $args = $attributes[0]->getArguments();
        $name = $args[0] ?? $args['name'] ?? null;
        if (!is_string($name) || $name === '') {
            throw new TwigComponentException([
                'The name of Twig component {component} must be a non-empty string; {type} given.',
                'component' => static::class,
                'type' => get_debug_type($name),
            ]);
        }

        return $name;
    }

    /**
     * Throws a component exception with the component name and message.
     *
     * The message goes inside a sentence of its own that names the component, so
     * both are translated, each one by its own entry.
     *
     * @param string $message The error message, in English, as the id of its
     * translation.
     * @param array<string, mixed> $parameters The values of the parameters of
     * the message.
     * @return never
     */
    protected function error(string $message, array $parameters = []): never
    {
        throw new TwigComponentException([
            'Component {component}: {message}',
            'component' => $this->getComponentName(),
            'message' => new TranslatableMessage($message, $parameters, 'errors'),
        ]);
    }

    /**
     * Gets the default icon for a given type.
     *
     * @param string|null $type The icon type.
     * @return string The default icon.
     */
    protected function getDefaultIcon(?string $type = null): string
    {
        return match($type) {
            'primary' => 'fa-solid fa-star text-primary fa-fw',
            'secondary' => 'fa-solid fa-info text-secondary fa-fw',
            'success' => 'fa-solid fa-check-circle text-success fa-fw',
            'danger' => 'fa-solid fa-triangle-exclamation text-danger fa-fw',
            'warning' => 'fa-solid fa-exclamation-circle text-warning fa-fw',
            'info' => 'fa-solid fa-info-circle text-info fa-fw',
            'light' => 'fa-solid fa-sun text-dark fa-fw',
            'dark' => 'fa-solid fa-moon text-dark fa-fw',
            default => 'fa-solid fa-exclamation fa-fw',
        };
    }
}
