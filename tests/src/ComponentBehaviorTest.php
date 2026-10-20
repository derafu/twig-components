<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents;

use DateTimeImmutable;
use Derafu\TestsTwigComponents\Fixture\RendersComponents;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\Test;

/**
 * The options of the components change what they render.
 */
#[CoversNamespace('Derafu\TwigComponents')]
final class ComponentBehaviorTest extends RendersComponents
{
    #[Test]
    public function anOffcanvasHasItsBackdropUnlessItIsTurnedOff(): void
    {
        $with = $this->render('<twig:block-offcanvas title="Panel" content="Content" />');
        $without = $this->render('<twig:block-offcanvas title="Panel" content="Content" :withBackdrop="false" />');

        $this->assertStringNotContainsString('data-bs-backdrop', $with);
        $this->assertStringContainsString('data-bs-backdrop="false"', $without);
    }

    #[Test]
    public function anOffcanvasScrollsOnlyWhenItIsAskedTo(): void
    {
        $default = $this->render('<twig:block-offcanvas title="Panel" content="Content" />');
        $scrollable = $this->render('<twig:block-offcanvas title="Panel" content="Content" :scrollable="true" />');

        $this->assertStringNotContainsString('data-bs-scroll', $default);
        $this->assertStringContainsString('data-bs-scroll="true"', $scrollable);
    }

    #[Test]
    public function aScrollspyHasTheClassesOfItsOrientation(): void
    {
        $sections = ['sections' => [['id' => 's1', 'title' => 'Section 1', 'content' => 'Text 1']]];

        $vertical = $this->render('<twig:block-scrollspy :sections="sections" />', $sections);
        $horizontal = $this->render('<twig:block-scrollspy :sections="sections" orientation="horizontal" />', $sections);

        $this->assertStringContainsString('class="list-group list-group-horizontal-md d-md-block"', $vertical);
        $this->assertStringContainsString('list-scrollspy-vertical', $vertical);
        $this->assertStringContainsString('class="list-group list-group-horizontal"', $horizontal);
        $this->assertStringContainsString('list-scrollspy-horizontal', $horizontal);
    }

    #[Test]
    public function theColumnsOfATableGiveTheClassesOfItsCells(): void
    {
        $html = $this->render(
            '<twig:block-table :rows="rows" :columns="columns" />',
            [
                'rows' => [['A', 'B']],
                'columns' => [['align' => 'center', 'class' => 'fw-bold'], ['align' => 'end']],
            ]
        );

        $this->assertMatchesRegularExpression('/<td class="\s*text-center fw-bold\s*">\s*A\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="\s*text-end\s*">\s*B\s*<\/td>/', $html);
    }

    #[Test]
    public function theRowClassesOfATableDependOnTheValueOfTheCell(): void
    {
        $html = $this->render(
            '<twig:block-table :rows="rows" :rowClasses="rowClasses" />',
            [
                'rows' => [['Yes', 'x'], ['No', 'y'], ['Maybe', 'z']],
                'rowClasses' => [['col' => 0, 'values' => ['Yes' => 'table-success', 'No' => 'table-danger']]],
            ]
        );

        $this->assertMatchesRegularExpression('/<td class="\s*table-success\s*">\s*Yes\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="\s*table-danger\s*">\s*No\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="\s*">\s*Maybe\s*<\/td>/', $html);
        // The column that has no condition has no class.
        $this->assertMatchesRegularExpression('/<td class="\s*">\s*x\s*<\/td>/', $html);
    }

    #[Test]
    public function theItemsWithOnlyTheDataThatTheyNeedRenderInStrictMode(): void
    {
        // Every optional key of the items is left out: the strict mode of Twig
        // does not fail for the keys that are not there.
        $html = $this->render(
            '<twig:block-card-grid :cards="cards" /><twig:block-comparison :plans="plans" /><twig:block-gallery :items="items" />',
            [
                'cards' => [['title' => 'Card']],
                'plans' => [['title' => 'Plan', 'price' => '$1', 'features' => [], 'buttonText' => 'Go', 'buttonUrl' => '#']],
                'items' => [['image' => '/a.jpg', 'caption' => 'A']],
            ]
        );

        $this->assertStringContainsString('derafu-block-card-grid', $html);
        $this->assertStringContainsString('derafu-block-comparison', $html);
        $this->assertStringContainsString('derafu-block-gallery', $html);
    }

    #[Test]
    public function aScrollspyGivesAnIdAndTheDefaultsToTheSectionsThatLackThem(): void
    {
        $html = $this->render(
            '<twig:block-scrollspy :sections="sections" />',
            ['sections' => [['content' => 'Only text']]]
        );

        $this->assertMatchesRegularExpression('/id="section-[0-9a-f]{13}"/', $html);
        $this->assertMatchesRegularExpression('/href="#section-[0-9a-f]{13}"/', $html);
        $this->assertStringContainsString('Untitled', $html);
        $this->assertStringContainsString('Only text', $html);
    }

    #[Test]
    public function aScrollspyWithAnOrientationThatDoesNotExistIsVertical(): void
    {
        $html = $this->render(
            '<twig:block-scrollspy :sections="sections" orientation="diagonal" />',
            ['sections' => [['id' => 's1', 'title' => 'Section 1', 'content' => 'Text 1']]]
        );

        $this->assertStringContainsString('list-scrollspy-vertical', $html);
        $this->assertStringNotContainsString('list-group-diagonal', $html);
    }

    #[Test]
    public function aScrollspyKeepsItsColumnsBetweenOneAndEleven(): void
    {
        $data = ['sections' => [['id' => 's1', 'title' => 'Section 1', 'content' => 'Text 1']]];

        $wide = $this->render('<twig:block-scrollspy :sections="sections" :cols="15" />', $data);
        $narrow = $this->render('<twig:block-scrollspy :sections="sections" :cols="0" />', $data);

        $this->assertStringContainsString('col-md-11', $wide);
        $this->assertStringContainsString('col-md-1 ', $narrow);
        $this->assertStringNotContainsString('col-md--', $wide);
    }

    #[Test]
    public function aScrollspyWithoutSectionsShowsOneByDefault(): void
    {
        $html = $this->render('<twig:block-scrollspy />');

        $this->assertStringContainsString('id="default-section"', $html);
    }

    #[Test]
    public function aTextVideoEmbedsTheVideoOfTheUrlWithPrivacyUnlessItIsTurnedOff(): void
    {
        $url = 'https://youtu.be/dQw4w9WgXcQ';

        $private = $this->render('<twig:block-text-video title="T" content="C" video="' . $url . '" />');
        $plain = $this->render('<twig:block-text-video title="T" content="C" video="' . $url . '" :enablePrivacy="false" />');

        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $private);
        $this->assertStringContainsString('https://www.youtube.com/embed/dQw4w9WgXcQ', $plain);
        $this->assertStringNotContainsString('nocookie', $plain);
    }

    #[Test]
    public function aTimelineAcceptsTheDatesAsObjects(): void
    {
        $html = $this->render(
            '<twig:block-timeline :events="events" />',
            ['events' => [['date' => new DateTimeImmutable('2023-05-17'), 'content' => 'Something happened']]]
        );

        $this->assertStringContainsString('2023', $html);
        $this->assertStringContainsString('Something happened', $html);
    }
}
