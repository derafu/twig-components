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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * The options of the components change what they render.
 */
#[CoversNamespace('Derafu\TwigComponents')]
final class ComponentBehaviorTest extends RendersComponents
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function provideTextMediaComponents(): array
    {
        return [
            'image' => ['block-text-image', 'image="/image.png"'],
            'video' => ['block-text-video', 'video="https://youtu.be/dQw4w9WgXcQ"'],
        ];
    }

    #[Test]
    #[DataProvider('provideTextMediaComponents')]
    public function textMediaOmitTheNewRegionsByDefault(string $component, string $media): void
    {
        $html = $this->render('<twig:' . $component . ' title="A <em>heading</em>" content="<p>Body content</p>" ' . $media . ' />');

        foreach (['eyebrow', 'lead', 'items', 'badge', 'note'] as $region) {
            $this->assertStringNotContainsString('derafu-' . $component . '__' . $region, $html);
        }
        $this->assertStringContainsString('A <em>heading</em>', $html);
        $this->assertStringContainsString('<p>Body content</p>', $html);
    }

    #[Test]
    #[DataProvider('provideTextMediaComponents')]
    public function textMediaRenderMarkupInTheTextRegions(string $component, string $media): void
    {
        $html = $this->render(
            '<twig:' . $component . ' title="Heading" content="<p>Body content</p>" :eyebrow="eyebrow" :lead="lead" :badge="badge" :note="note" :items="items" :buttons="buttons" ' . $media . ' />',
            [
                'eyebrow' => '<em>Overview</em>',
                'lead' => '<p>Explore <strong>shared ideas</strong>.</p>',
                'badge' => '<strong>Available</strong>',
                'note' => '<p>Read the <a href="/guide">guide</a>.</p><p>Review the details.</p>',
                'items' => [['text' => '<strong>A list item</strong>']],
                'buttons' => [['text' => 'Continue', 'url' => '#continue']],
            ]
        );

        foreach (['<em>Overview</em>', '<strong>shared ideas</strong>', '<strong>Available</strong>', '<strong>A list item</strong>'] as $markup) {
            $this->assertStringContainsString($markup, $html);
        }
        $this->assertMatchesRegularExpression('/__eyebrow.*__title.*__lead.*__text.*__items.*__badge.*__note.*__buttons/s', $html);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $xpath = new \DOMXPath($document);
        foreach (['lead' => 1, 'note' => 2] as $region => $paragraphs) {
            $nodes = $xpath->query('//*[contains(@class, "derafu-' . $component . '__' . $region . '")]');
            $this->assertNotFalse($nodes);
            $node = $nodes->item(0);
            $this->assertInstanceOf(\DOMElement::class, $node);
            $this->assertSame('div', $node->tagName);
            $this->assertSame($paragraphs, $node->getElementsByTagName('p')->length);
        }
    }

    #[Test]
    #[DataProvider('provideTextMediaComponents')]
    public function textMediaItemsAcceptOptionalIconsAndOuterOrInternalLinks(string $component, string $media): void
    {
        $html = $this->render(
            '<twig:' . $component . ' title="Heading" content="Body" :items="items" ' . $media . ' />',
            ['items' => [
                ['text' => 'Plain text'],
                ['text' => 'Icon only', 'icon' => 'fa-solid fa-check'],
                ['text' => 'Link only', 'url' => '#guide?a=1&b=2'],
                ['text' => '<strong>Icon and link</strong>', 'icon' => 'fa-solid fa-star', 'url' => '#features'],
                ['text' => 'Empty options', 'icon' => '', 'url' => ''],
                ['text' => 'Null options', 'icon' => null, 'url' => null],
                ['text' => 'Zero path', 'url' => '0'],
                ['text' => '<p>Read the <a href="/internal">guide</a>.</p>'],
            ]]
        );

        $this->assertStringContainsString('href="#guide?a=1&amp;b=2"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringNotContainsString('href=""', $html);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $this->assertSame(8, $document->getElementsByTagName('li')->length);
        $this->assertSame(2, $document->getElementsByTagName('i')->length);
        $links = $document->getElementsByTagName('a');
        $this->assertSame(4, $links->length);
        foreach (['#guide?a=1&b=2', '#features', '0', '/internal'] as $index => $url) {
            $link = $links->item($index);
            $this->assertInstanceOf(\DOMElement::class, $link);
            $this->assertSame($url, $link->getAttribute('href'));
            $this->assertSame($index === 1 ? 1 : 0, $link->getElementsByTagName('i')->length);
        }
    }

    #[Test]
    #[DataProvider('provideTextMediaComponents')]
    public function textMediaOmitEmptyTextRegionsAndKeepZeroText(string $component, string $media): void
    {
        $template = '<twig:' . $component . ' title="Heading" content="Body" :eyebrow="value" :lead="value" :badge="value" :note="value" ' . $media . ' />';
        foreach ([null, ''] as $value) {
            $html = $this->render($template, ['value' => $value]);
            foreach (['eyebrow', 'lead', 'items', 'badge', 'note'] as $region) {
                $this->assertStringNotContainsString('derafu-' . $component . '__' . $region, $html);
            }
        }
        $html = $this->render($template, ['value' => '0']);
        $this->assertStringContainsString('__eyebrow">0</div>', $html);
        $this->assertStringContainsString('__lead lead">0</div>', $html);
        $this->assertStringContainsString('class="badge">0</div>', $html);
        $this->assertStringContainsString('__note small">0</div>', $html);
    }

    #[Test]
    public function aTitleKeepsItsOptionalIntroductionAndHeadingIdAbsentByDefault(): void
    {
        $html = $this->render('<twig:block-title id="title-root" title="A heading" />');

        $this->assertStringNotContainsString('derafu-block-title__eyebrow', $html);
        $this->assertStringNotContainsString('derafu-block-title__subtitle', $html);
        $this->assertStringContainsString('id="title-root"', $html);
        $this->assertMatchesRegularExpression('/<h2 class="derafu-block-title__title">\s*A heading\s*<\/h2>/', $html);
    }

    #[Test]
    public function aTitleHasAnEscapedIntroductionAndAnIndependentHeadingId(): void
    {
        $html = $this->render(
            '<twig:block-title id="title-root" headingId="heading-reference" role="region" aria-labelledby="heading-reference" :level="3" :eyebrow="eyebrow" title="A heading with <em>emphasis</em>" />',
            ['eyebrow' => '<strong>Research & development</strong>']
        );

        $this->assertStringContainsString('&lt;strong&gt;Research &amp; development&lt;/strong&gt;', $html);
        $this->assertStringContainsString('<em>emphasis</em>', $html);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $root = $document->getElementById('title-root');
        $heading = $document->getElementById('heading-reference');
        $this->assertInstanceOf(\DOMElement::class, $root);
        $this->assertInstanceOf(\DOMElement::class, $heading);
        $this->assertSame('div', $root->tagName);
        $this->assertSame('h3', $heading->tagName);
        $this->assertSame($heading->getAttribute('id'), $root->getAttribute('aria-labelledby'));
    }

    #[Test]
    public function aTitleOmitsEmptyOptionsAndAcceptsZeroAsTextOrAnIdentifier(): void
    {
        foreach ([null, ''] as $value) {
            $html = $this->render(
                '<twig:block-title title="A heading" :eyebrow="value" :headingId="value" />',
                ['value' => $value]
            );
            $this->assertStringNotContainsString('derafu-block-title__eyebrow', $html);
            $this->assertStringContainsString('<h2 class="derafu-block-title__title">', $html);
        }

        $html = $this->render('<twig:block-title title="A heading" eyebrow="0" headingId="0" />');
        $this->assertStringContainsString('class="derafu-block-title__eyebrow">0</p>', $html);
        $this->assertStringContainsString('<h2 class="derafu-block-title__title" id="0">', $html);
    }

    #[Test]
    public function aTitleAcceptsBothAlignmentFormats(): void
    {
        foreach (['start' => 'start', 'left' => 'start', 'center' => 'center', 'end' => 'end', 'right' => 'end'] as $align => $expected) {
            $html = $this->render('<twig:block-title title="A heading" :align="align" />', ['align' => $align]);

            $this->assertStringContainsString('class="derafu-block-title__wrapper text-' . $expected . '"', $html);
        }
    }

    #[Test]
    public function aHeroOmitsItsOptionalRegionsByDefault(): void
    {
        $html = $this->render('<twig:block-hero title="Welcome" />');

        $this->assertStringNotContainsString('derafu-block-hero__header', $html);
        $this->assertStringNotContainsString('derafu-block-hero__eyebrow', $html);
        $this->assertStringNotContainsString('derafu-block-hero__highlights', $html);
    }

    #[Test]
    public function aHeroRendersPlainTextRegionsAndDecorativeIcons(): void
    {
        $html = $this->render(
            '<twig:block-hero title="Welcome" :eyebrow="eyebrow" :navigation="navigation" :highlights="highlights" />',
            [
                'eyebrow' => 'Business & growth',
                'navigation' => [['text' => '<em>Documentation</em>', 'url' => '#docs']],
                'highlights' => [
                    ['text' => '<strong>Plain text</strong>'],
                    ['text' => 'With icon', 'icon' => 'fa-solid fa-check'],
                    ['text' => 'Empty icon', 'icon' => ''],
                    ['text' => 'Null icon', 'icon' => null],
                ],
            ]
        );

        $this->assertSame(1, substr_count($html, 'Business &amp; growth</p>'));
        $this->assertMatchesRegularExpression('/href="#docs">\s*<span>&lt;em&gt;Documentation&lt;\/em&gt;<\/span>\s*<\/a>/', $html);
        $this->assertStringContainsString('&lt;strong&gt;Plain text&lt;/strong&gt;', $html);
        $this->assertSame(1, substr_count($html, '<i '));
        $this->assertStringContainsString('class="fa-solid fa-check" aria-hidden="true"', $html);
    }

    #[Test]
    public function bothHeroListsAcceptOptionalIconsAndLinks(): void
    {
        foreach (['navigation', 'highlights'] as $region) {
            $html = $this->render(
                '<twig:block-hero title="Welcome" :' . $region . '="items" />',
                ['items' => [
                    ['text' => 'Plain text'],
                    ['text' => 'Icon only', 'icon' => 'fa-solid fa-check'],
                    ['text' => 'Link only', 'url' => '#link'],
                    ['text' => 'Icon and link', 'icon' => 'fa-solid fa-star', 'url' => '#both'],
                    ['text' => 'Empty options', 'icon' => '', 'url' => ''],
                    ['text' => 'Null options', 'icon' => null, 'url' => null],
                    ['text' => 'Zero path', 'url' => '0'],
                ]]
            );

            $document = new \DOMDocument();
            $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
            $this->assertSame(7, $document->getElementsByTagName('li')->length);
            $this->assertSame(2, $document->getElementsByTagName('i')->length);
            $links = $document->getElementsByTagName('a');
            $this->assertSame(3, $links->length);
            foreach (['#link', '#both', '0'] as $index => $url) {
                $link = $links->item($index);
                $this->assertInstanceOf(\DOMElement::class, $link);
                $this->assertSame($url, $link->getAttribute('href'));
                $this->assertSame($index === 1 ? 1 : 0, $link->getElementsByTagName('i')->length);
            }
            $this->assertStringNotContainsString('href=""', $html);
        }
    }

    #[Test]
    public function aHeroPlacesAnIntroductionWithoutNavigationAboveItsTitle(): void
    {
        $html = $this->render('<twig:block-hero title="Welcome" eyebrow="Introduction" align="center" />');

        $this->assertStringNotContainsString('<nav', $html);
        $this->assertMatchesRegularExpression('/derafu-block-hero__content--center.*derafu-block-hero__eyebrow.*Introduction.*<h1/s', $html);
    }

    #[Test]
    public function aHeroAllowsReplacingItsInternalRegions(): void
    {
        $html = $this->render(<<<'TWIG'
            {% component 'block-hero' with {title: 'Welcome'} %}
                {% block hero_header %}<p>Custom top content</p>{% endblock %}
                {% block hero_footer %}<p>Custom bottom content</p>{% endblock %}
            {% endcomponent %}
            TWIG);

        $this->assertMatchesRegularExpression('/derafu-block-hero__container.*Custom top content.*<h1.*Welcome.*Custom bottom content/s', $html);
    }

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

        $this->assertStringContainsString('id="default-section-block-scrollspy--', $html);
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

    #[Test]
    public function headersHaveIndependentNavigationTargets(): void
    {
        $html = $this->render('<twig:block-header id="first" /><twig:block-header id="second" />');

        foreach (['first', 'second'] as $id) {
            $this->assertStringContainsString('data-bs-target="#' . $id . '-navbarContent"', $html);
            $this->assertSame(1, substr_count($html, 'id="' . $id . '-navbarContent"'));
        }
        $this->assertStringContainsString('aria-label="Mostrar u ocultar navegación"', $html);
    }

    #[Test]
    public function scrollspiesHaveIndependentTargetsAndDefaultSections(): void
    {
        $html = $this->render('<twig:block-scrollspy id="first" /><twig:block-scrollspy id="second" />');

        foreach (['first', 'second'] as $id) {
            $this->assertStringContainsString('data-bs-target="#' . $id . '-list-scrollspy-vertical"', $html);
            $this->assertSame(1, substr_count($html, 'id="default-section-' . $id . '"'));
            $this->assertStringContainsString('href="#default-section-' . $id . '"', $html);
        }
    }

    #[Test]
    public function imageAlternativeTextCanBeIndependentEmptyOrDefaulted(): void
    {
        $template = '<twig:block-text-image title="A <em>title</em>" content="Text" image="/image.png"';
        $default = $this->render($template . ' />');
        $custom = $this->render($template . ' imageAlt="An independent description" />');
        $decorative = $this->render($template . ' imageAlt="" />');

        $this->assertStringContainsString('alt="A title"', $default);
        $this->assertStringContainsString('alt="An independent description"', $custom);
        $this->assertStringContainsString('alt=""', $decorative);
    }

    #[Test]
    public function linkedCardsKeepTheirContentAndActionLinksIndependent(): void
    {
        $html = $this->render('<twig:block-card-grid :cards="cards" />', [
            'cards' => [[
                'url' => '/card',
                'title' => 'Card',
                'content' => '<a href="/details">Details</a>',
                'buttonText' => 'Contact',
                'buttonUrl' => '/contact',
            ]],
        ]);

        $this->assertStringContainsString('href="/details"', $html);
        $this->assertStringContainsString('href="/contact"', $html);
        $this->assertStringContainsString('href="/card" class="derafu-block-card-grid__link stretched-link"', $html);
        $this->assertDoesNotMatchRegularExpression('/<a[^>]*>[^<]*<a/s', $html);
        $this->assertStringNotContainsString('<a href="/card" class="derafu-block-card-grid__card', $html);
    }

    #[Test]
    public function boxesWithoutIconsDoNotHaveAnEmptyIconContainer(): void
    {
        $html = $this->render('<twig:block-boxes :boxes="boxes" />', [
            'boxes' => [['title' => 'Text only'], ['title' => 'Cloud', 'icon' => 'fa-solid fa-cloud']],
        ]);

        $this->assertSame(1, substr_count($html, 'class="derafu-block-boxes__icon-container"'));
        $this->assertStringContainsString('Text only', $html);
        $this->assertStringContainsString('fa-solid fa-cloud', $html);
    }

    #[Test]
    public function testimonialsDisplayTheirNoteWithAndWithoutALink(): void
    {
        $html = $this->render('<twig:block-testimonials :testimonials="testimonials" />', [
            'testimonials' => [
                ['content' => 'First quote', 'author' => 'First author', 'note' => 'First organization', 'url' => '/first'],
                ['content' => 'Second quote', 'author' => 'Second author', 'note' => 'Second organization'],
            ],
        ]);

        $this->assertStringContainsString('First organization</a>', $html);
        $this->assertStringContainsString('• Second organization', $html);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideStandaloneMediaComponents(): array
    {
        return [
            'image' => ['block-image', 'image="/image.png"'],
            'video' => ['block-video', 'video="https://www.youtube.com/watch?v=GOAEIMx39-w"'],
        ];
    }

    #[Test]
    #[DataProvider('provideStandaloneMediaComponents')]
    public function standaloneMediaKeepTheirDescriptionAfterTheMediaByDefault(string $component, string $media): void
    {
        $html = $this->render('<twig:' . $component . ' title="Heading" content="<p>Description</p>" ' . $media . ' />');

        $this->assertMatchesRegularExpression('/<(?:img|iframe).*<p>Description<\/p>/s', $html);
        $this->assertStringNotContainsString('__eyebrow', $html);
        $this->assertSame(1, substr_count($html, '<p>Description</p>'));
    }

    #[Test]
    #[DataProvider('provideStandaloneMediaComponents')]
    public function standaloneMediaCanOwnTheirIntroductionBeforeTheMedia(string $component, string $media): void
    {
        $html = $this->render('<twig:' . $component . ' eyebrow="<strong>Overview</strong>" title="Heading" content="<p>Description</p>" contentPosition="before" ' . $media . ' />');

        $this->assertMatchesRegularExpression('/<strong>Overview<\/strong>.*Heading.*<p>Description<\/p>.*<(?:img|iframe)/s', $html);
        $this->assertSame(1, substr_count($html, '<p>Description</p>'));
    }

    #[Test]
    public function aVideoUsesItsAspectRatioWithoutOverridingTheDefaultThemeVariable(): void
    {
        $custom = $this->render('<twig:block-video title="<em>Overview</em>" video="https://www.youtube.com/watch?v=GOAEIMx39-w" aspectRatio="4:3" />');
        $default = $this->render('<twig:block-video title="Overview" video="https://www.youtube.com/watch?v=GOAEIMx39-w" />');

        $this->assertStringContainsString('--video-aspect-ratio: 4 / 3;', $custom);
        $this->assertStringContainsString('title="Overview"', $custom);
        $this->assertStringNotContainsString('--video-aspect-ratio:', $default);
    }

    #[Test]
    public function imagesAndCardsAcceptIndependentOrDecorativeAlternativeText(): void
    {
        foreach (['block-image', 'block-card'] as $component) {
            $default = $this->render('<twig:' . $component . ' title="<em>Overview</em>" image="/image.png" />');
            $custom = $this->render('<twig:' . $component . ' title="Overview" image="/image.png" imageAlt="A diagram" />');
            $decorative = $this->render('<twig:' . $component . ' title="Overview" image="/image.png" imageAlt="" />');
            $this->assertStringContainsString('alt="Overview"', $default);
            $this->assertStringContainsString('alt="A diagram"', $custom);
            $this->assertStringContainsString('alt=""', $decorative);
        }
    }

    #[Test]
    public function headerOptionsAreAttributesRatherThanPartOfTheClass(): void
    {
        $html = $this->render('<twig:block-header id="main-menu" logoText="Workspace" backgroundColor="#123456" dropdownTrigger="hover" />');
        $document = new \DOMDocument();
        $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $nav = $document->getElementsByTagName('nav')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $nav);
        $this->assertSame('background-color: #123456;', $nav->getAttribute('style'));
        $this->assertSame('hover', $nav->getAttribute('data-dropdown-trigger'));
        $this->assertStringNotContainsString('style=', $nav->getAttribute('class'));
    }

    #[Test]
    public function tableVisibilityCanBeToggledWithoutAnActionsMenu(): void
    {
        $html = $this->render('<twig:block-table id="report" :headers="[]" :rows="[[1, 2]]" :toggleable="true" />');

        $this->assertStringContainsString('table-report', $html);
        $this->assertStringContainsString("classList.toggle('d-none')", $html);
        $this->assertStringNotContainsString('dropdown-toggle', $html);
    }

    #[Test]
    public function untitledDialogsDoNotReferenceMissingHeadingIdentifiers(): void
    {
        foreach (['block-modal', 'block-offcanvas'] as $component) {
            $html = $this->render('<twig:' . $component . ' content="Body" aria-label="Details" />');
            $this->assertStringContainsString('aria-label="Details"', $html);
            $this->assertStringNotContainsString('aria-labelledby=', $html);
        }
    }

    #[Test]
    #[DataProvider('provideTextMediaComponents')]
    public function anEmptyTextMediaDescriptionDoesNotReserveAContentRegion(string $component, string $media): void
    {
        $html = $this->render('<twig:' . $component . ' title="Heading" content="" lead="Introduction" note="Details" ' . $media . ' />');

        $this->assertStringNotContainsString('class="derafu-' . $component . '__text"', $html);
        $this->assertStringContainsString('Introduction', $html);
        $this->assertStringContainsString('Details', $html);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provideContainedComponents(): array
    {
        return [
            'title' => ['block-title', 'title="Heading"'],
            'features' => ['block-features-icon', ':features="items"'],
            'cards' => ['block-card-grid', ':cards="items"'],
            'boxes' => ['block-boxes', ':boxes="items"'],
            'steps' => ['block-steps', ':steps="items"'],
            'image' => ['block-image', 'title="Heading" image="/image.png"'],
            'video' => ['block-video', 'title="Heading" video="https://www.youtube.com/watch?v=GOAEIMx39-w"'],
            'text-image' => ['block-text-image', 'title="Heading" content="Body" image="/image.png"'],
            'text-video' => ['block-text-video', 'title="Heading" content="Body" video="https://www.youtube.com/watch?v=GOAEIMx39-w"'],
        ];
    }

    #[Test]
    #[DataProvider('provideContainedComponents')]
    public function containmentIsOptInAndKeepsTheRootAttributes(string $component, string $attributes): void
    {
        foreach ([false, true] as $contained) {
            $html = $this->render(
                '<twig:' . $component . ' id="containment-check" class="bg-body-tertiary" :contained="contained" ' . $attributes . ' />',
                ['contained' => $contained, 'items' => [['title' => 'First item', 'content' => 'Body', 'icon' => 'fa-solid fa-check']]]
            );
            $document = new \DOMDocument();
            $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
            $xpath = new \DOMXPath($document);
            $roots = $xpath->query('//*[@id="containment-check"]');
            $this->assertNotFalse($roots);
            $root = $roots->item(0);
            $this->assertInstanceOf(\DOMElement::class, $root);
            $this->assertStringContainsString('bg-body-tertiary', $root->getAttribute('class'));
            $inner = $xpath->query('./div[contains(concat(" ", normalize-space(@class), " "), " container ")]', $root);
            $this->assertNotFalse($inner);
            $this->assertSame($contained ? 1 : 0, $inner->length);
        }
    }

    /**
     * @return array<string, array{int}>
     */
    public static function provideFeatureColumnCounts(): array
    {
        return ['one' => [1], 'two' => [2], 'three' => [3], 'four' => [4], 'six' => [6]];
    }

    #[Test]
    #[DataProvider('provideFeatureColumnCounts')]
    public function explicitFeatureColumnsPreserveEveryItemAndThePartialRow(int $cols): void
    {
        $features = array_map(
            static fn (int $index): array => ['title' => 'Feature ' . $index, 'content' => 'Description'],
            range(1, 7)
        );
        $html = $this->render('<twig:block-features-icon :features="features" :cols="cols" align="left" :contained="true" />', ['features' => $features, 'cols' => $cols]);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $xpath = new \DOMXPath($document);
        $rows = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " derafu-block-features-icon__row ")]');
        $items = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " derafu-block-features-icon__item--' . $cols . ' ")]');
        $this->assertNotFalse($rows);
        $this->assertNotFalse($items);
        $this->assertSame((int) ceil(7 / $cols), $rows->length);
        $this->assertSame(7, $items->length);
        $this->assertStringContainsString('derafu-block-features-icon--left', $html);
        foreach (range(1, 7) as $index) {
            $this->assertSame(1, substr_count($html, 'Feature ' . $index));
        }
    }

    #[Test]
    public function featureColumnsCanReturnToAutomaticGrouping(): void
    {
        $component = new \Derafu\TwigComponents\Component\Block\FeaturesIconComponent();
        $this->assertNull($component->getCols());
        $this->assertSame('center', $component->getAlign());
        $this->assertSame($component, $component->setCols(3)->setCols(null));
        $this->assertNull($component->getCols());
    }

    #[Test]
    public function unsupportedFeatureColumnCountsRaiseTheComponentException(): void
    {
        $component = new \Derafu\TwigComponents\Component\Block\FeaturesIconComponent();
        $this->expectException(\Derafu\TwigComponents\Exception\TwigComponentException::class);
        $component->setCols(5);
    }

    #[Test]
    public function featureAlignmentAcceptsStartAndEndAliases(): void
    {
        foreach (['start' => 'left', 'end' => 'right'] as $align => $expected) {
            $html = $this->render('<twig:block-features-icon :features="[]" :align="align" />', ['align' => $align]);
            $this->assertStringContainsString('derafu-block-features-icon--' . $expected, $html);
        }
    }
}
