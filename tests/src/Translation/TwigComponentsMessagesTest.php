<?php

declare(strict_types=1);

/**
 * Derafu: Twig Components - UI components for Twig and Symfony.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsTwigComponents\Translation;

use Derafu\Translation\Lint\MessageMethod;
use Derafu\Translation\Lint\TranslationAudit;
use Derafu\TwigComponents\Abstract\AbstractComponent;
use Derafu\TwigComponents\Translation\TwigTranslationResourceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The package is translated: every message of the components has its Spanish
 * translation, the catalogues have nothing that the code or the templates do not
 * use, and every exception that the package throws is translatable.
 */
#[CoversClass(TwigTranslationResourceProvider::class)]
final class TwigComponentsMessagesTest extends TestCase
{
    #[Test]
    public function theMessagesOfTheCodeAreTranslated(): void
    {
        $report = (new TranslationAudit())->audit(
            dirname(__DIR__, 3) . '/src',
            new TwigTranslationResourceProvider(),
            messageMethods: [new MessageMethod(AbstractComponent::class, 'error', domain: 'errors')]
        );

        // Finding nothing would look like a clean result.
        $this->assertFalse($report->nothingFound);
        $this->assertSame([], $report->describe($report->dynamicMessages));
        $this->assertSame([], $report->describe($report->missingTranslations));

        // The `twig` domain is used by the templates, which the next test reads.
        $notUsed = array_filter($report->notUsedBySources, fn (array $entry) => $entry['domain'] !== 'twig');
        $this->assertSame([], array_values($notUsed));
        $this->assertSame([], $report->describe($report->notTranslatable));
    }

    #[Test]
    public function theTextsOfTheTemplatesAreTranslated(): void
    {
        $root = dirname(__DIR__, 3);
        $catalogue = require $root . '/resources/translations/twig+intl-icu.es.php';

        // What the templates translate: the texts that go through `|trans`.
        $used = [];
        foreach (glob($root . '/resources/templates/components/*.html.twig') ?: [] as $file) {
            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'\\s*\\|\\s*trans\\b/", (string) file_get_contents($file), $matches);
            foreach ($matches[1] as $text) {
                $used[stripcslashes($text)] = true;
            }
        }

        $this->assertNotEmpty($used);
        $this->assertSame([], array_values(array_diff(array_keys($used), array_keys($catalogue))), 'Texts without translation');
        $this->assertSame([], array_values(array_diff(array_keys($catalogue), array_keys($used))), 'Translations that nothing uses');
    }
}
