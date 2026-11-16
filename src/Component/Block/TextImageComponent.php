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

#[AsTwigComponent('block-text-image')]
class TextImageComponent extends AbstractComponent
{
    /**
     * Title of the text section.
     *
     * @var string
     */
    private string $title;

    /**
     * Main content text (supports paragraphs).
     *
     * @var string
     */
    private string $content;

    /**
     * Array of button configurations.
     * Each button contains: {text, url}
     *
     * @var array<int,array<string,mixed>>
     */
    private array $buttons = [];

    /**
     * Number of columns for text section (Bootstrap grid).
     *
     * @var int
     */
    private int $cols = 7;

    /**
     * URL of the featured image.
     *
     * @var string
     */
    private string $image;

    /**
     * Alternative image text. Null uses the title; an empty string marks a decorative image.
     */
    private ?string $imageAlt = null;

    /**
     * Position of the image (right, left).
     *
     * @var string
     */
    private string $imagePosition = 'right';

    /**
     * Optional flag to indicate rendering inside tabs.
     *
     * @var bool
     */
    private bool $insideTabs = false;

    /**
     * Optional introduction text above the title, rendered with raw in Twig.
     */
    private ?string $eyebrow = null;

    /**
     * Optional lead text between the title and content, rendered with raw in Twig.
     */
    private ?string $lead = null;

    /**
     * List items with text, optional decorative icons and links.
     * Item text is rendered with raw in Twig.
     *
     * @var array<int, array{text: string, icon?: string|null, url?: string|null}>
     */
    private array $items = [];

    /**
     * Optional status label text after the list, rendered with raw in Twig.
     */
    private ?string $badge = null;

    /**
     * Optional supporting note text before the actions, rendered with raw in Twig.
     */
    private ?string $note = null;

    public function getEyebrow(): ?string
    {
        return $this->eyebrow;
    }

    public function setEyebrow(?string $eyebrow): static
    {
        $this->eyebrow = $eyebrow;

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setLead(?string $lead): static
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return array<int, array{text: string, icon?: string|null, url?: string|null}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array<int, array{text: string, icon?: string|null, url?: string|null}> $items
     */
    public function setItems(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(?string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Gets the title of the text section.
     *
     * @return string The title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title of the text section.
     *
     * @param string $title The title.
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the main content text.
     *
     * @return string The content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the main content text.
     *
     * @param string $content The content.
     * @return static
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gets the array of button configurations.
     *
     * @return array<int,array<string,mixed>> The array of buttons.
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * Sets the array of button configurations.
     *
     * @param array<int,array<string,mixed>> $buttons The array of buttons.
     * @return static
     */
    public function setButtons(array $buttons): static
    {
        $this->buttons = $buttons;

        return $this;
    }

    /**
     * Gets the number of columns for text section.
     *
     * @return int The number of columns.
     */
    public function getCols(): int
    {
        return $this->cols;
    }

    /**
     * Sets the number of columns for text section.
     *
     * @param int $cols The number of columns.
     * @return static
     */
    public function setCols(int $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * Gets the URL of the featured image.
     *
     * @return string The image URL.
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Sets the URL of the featured image.
     *
     * @param string $image The image URL.
     * @return static
     */
    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Gets the alternative image text, or null to use the title.
     */
    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    /**
     * Sets the alternative image text, or null to use the title.
     */
    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    /**
     * Gets the position of the image.
     *
     * @return string The image position.
     */
    public function getImagePosition(): string
    {
        return $this->imagePosition;
    }

    /**
     * Sets the position of the image.
     *
     * @param string $imagePosition The image position.
     * @return static
     */
    public function setImagePosition(string $imagePosition): static
    {
        $this->imagePosition = $imagePosition;

        return $this;
    }

    /**
     * Gets the flag indicating if component is rendered inside tabs.
     *
     * @return bool Whether component is inside tabs.
     */
    public function getInsideTabs(): bool
    {
        return $this->insideTabs;
    }

    /**
     * Sets the flag indicating if component is rendered inside tabs.
     *
     * @param bool $insideTabs Whether component is inside tabs.
     * @return static
     */
    public function setInsideTabs(bool $insideTabs): static
    {
        $this->insideTabs = $insideTabs;

        return $this;
    }
}
