<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\UI\Filament\Support;

use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use App\Containers\AppSection\Vacancy\UI\Filament\Support\SalaryRangeFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SalaryRangeFormatter::class)]
final class SalaryRangeFormatterTest extends UnitTestCase
{
    /**
     * @return array<string, array{int|null, int|null, Currency|null, string|null}>
     */
    public static function rangeProvider(): array
    {
        return [
            'both bounds' => [4000, 5500, Currency::USD, '4 000–5 500 USD'],
            'only minimum' => [3000, null, Currency::USD, 'від 3 000 USD'],
            'only maximum' => [null, 5000, Currency::EUR, 'до 5 000 EUR'],
            'no salary' => [null, null, Currency::USD, null],
            'thousands separator' => [95000, 1250000, Currency::UAH, '95 000–1 250 000 UAH'],
            'small amount' => [800, null, Currency::USD, 'від 800 USD'],
        ];
    }

    /**
     * @param int|null $min
     * @param int|null $max
     * @param Currency|null $currency
     * @param string|null $expected
     *
     * @return void
     */
    #[DataProvider('rangeProvider')]
    public function testFormatsSalaryRange(int|null $min, int|null $max, Currency|null $currency, string|null $expected): void
    {
        $this->assertSame($expected, SalaryRangeFormatter::format($min, $max, $currency));
    }
}
