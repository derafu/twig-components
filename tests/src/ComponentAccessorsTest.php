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

use Derafu\TwigComponents\Abstract\AbstractComponent;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * The options of every component are its properties: Twig Components fills them
 * through the setters, and the templates read them through the getters.
 */
#[CoversNamespace('Derafu\TwigComponents')]
final class ComponentAccessorsTest extends TestCase
{
    /**
     * Every setter of every component, with the value that its getter must give.
     *
     * @return array<string, array{class-string<AbstractComponent>, string, string}>
     */
    public static function provideAccessors(): array
    {
        $cases = [];

        foreach (glob(dirname(__DIR__, 2) . '/src/Component/Block/*Component.php') ?: [] as $file) {
            $class = 'Derafu\\TwigComponents\\Component\\Block\\' . basename($file, '.php');
            if (!is_subclass_of($class, AbstractComponent::class)) {
                continue;
            }
            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (!str_starts_with($method->getName(), 'set') || $method->getNumberOfParameters() !== 1) {
                    continue;
                }
                $name = substr($method->getName(), 3);
                $getter = 'get' . $name;
                if (!method_exists($class, $getter)) {
                    continue;
                }
                $cases[basename($file, 'Component.php') . '::' . $method->getName()] = [$class, $method->getName(), $getter];
            }
        }

        return $cases;
    }

    /**
     * @param class-string<AbstractComponent> $class
     */
    #[Test]
    #[DataProvider('provideAccessors')]
    public function theGetterGivesWhatTheSetterReceived(string $class, string $setter, string $getter): void
    {
        $component = new $class();
        $parameter = (new ReflectionMethod($class, $setter))->getParameters()[0];
        $type = $parameter->getType();
        $this->assertInstanceOf(ReflectionNamedType::class, $type);

        $value = match ($type->getName()) {
            'string' => 'sample',
            'int' => 1,
            'float' => 1.5,
            'bool' => true,
            'array' => [],
            default => $this->fail('A type without sample value: ' . $type->getName()),
        };

        $this->assertSame($component, $component->$setter($value));
        $this->assertSame($value, $component->$getter());
    }

    #[Test]
    public function theIdIsGeneratedOnceAndCanBeSet(): void
    {
        $component = new class () extends AbstractComponent {
        };

        $generated = $component->getId();
        $this->assertSame($generated, $component->getId());
        $this->assertSame($component, $component->setId('fixed'));
        $this->assertSame('fixed', $component->getId());
    }
}
