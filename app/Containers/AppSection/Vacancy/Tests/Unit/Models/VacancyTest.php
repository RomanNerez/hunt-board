<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Models;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Vacancy::class)]
final class VacancyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDescriptionKeepsFormatting(): void
    {
        $html = '<h2>Про проєкт</h2><p>Ми шукаємо <strong>PHP</strong>-розробника, <em>команда &lt; 10 людей</em>.</p><ul><li>Laravel</li><li>Postgres</li></ul><blockquote><p>Цитата</p></blockquote><p><a href="https://macpaw.com">Сайт</a></p>';

        $vacancy = Vacancy::factory()->createOne(['description' => $html]);

        $this->assertSame($html, $vacancy->refresh()->description);
    }

    /**
     * @return void
     */
    public function testDescriptionDropsUnsafeMarkup(): void
    {
        $vacancy = Vacancy::factory()->createOne([
            'description' => '<p onclick="steal()">Текст</p><script>alert(1)</script><a href="javascript:alert(1)">лінк</a>',
        ]);

        $description = (string) $vacancy->refresh()->description;

        $this->assertStringContainsString('<p>Текст</p>', $description);
        $this->assertStringNotContainsString('<script', $description);
        $this->assertStringNotContainsString('alert', $description);
        $this->assertStringNotContainsString('onclick', $description);
        $this->assertStringNotContainsString('javascript:', $description);
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function emptyDescriptionProvider(): array
    {
        return [
            'empty editor' => ['<p></p>'],
            'only line breaks' => ['<p><br></p><p>&nbsp;</p>'],
            'only script' => ['<script>alert(1)</script>'],
            'whitespace' => ['   '],
            'empty string' => [''],
            'null' => [null],
        ];
    }

    /**
     * @param string|null $input
     *
     * @return void
     */
    #[DataProvider('emptyDescriptionProvider')]
    public function testDescriptionWithoutVisibleTextIsStoredAsNull(string|null $input): void
    {
        $vacancy = Vacancy::factory()->createOne(['description' => $input]);

        $this->assertNull($vacancy->refresh()->description);
    }
}
