<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Support\Translator;
use PHPUnit\Framework\TestCase;

final class TranslatorTest extends TestCase
{
    public function testPolishCountForms(): void
    {
        $translator = $this->translator('pl');

        self::assertSame('· 1 wynik', $translator->translateCount('rooms.results.count', 1));
        self::assertSame('· 3 wyniki', $translator->translateCount('rooms.results.count', 3));
        self::assertSame('· 5 wyników', $translator->translateCount('rooms.results.count', 5));
        self::assertSame('· 12 wyników', $translator->translateCount('rooms.results.count', 12));
        self::assertSame('· 22 wyniki', $translator->translateCount('rooms.results.count', 22));
        self::assertSame('· 0 wyników', $translator->translateCount('rooms.results.count', 0));
    }

    public function testEnglishCountForms(): void
    {
        $translator = $this->translator('en');

        self::assertSame('· 1 result', $translator->translateCount('rooms.results.count', 1));
        self::assertSame('· 2 results', $translator->translateCount('rooms.results.count', 2));
        self::assertSame('· 21 results', $translator->translateCount('rooms.results.count', 21));
    }

    public function testUnknownKeyFallsBackToTheKeyName(): void
    {
        self::assertSame('rooms.missing.many', $this->translator('en')->translateCount('rooms.missing', 5));
    }

    private function translator(string $locale): Translator
    {
        return Translator::fromDirectory(dirname(__DIR__, 2) . '/translations', $locale);
    }
}
