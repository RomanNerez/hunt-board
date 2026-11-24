<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Enums;

use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Currency::class)]
final class CurrencyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasUsdEurAndUahLabelledByCode(): void
    {
        $labels = [];

        foreach (Currency::cases() as $currency) {
            $labels[$currency->value] = $currency->getLabel();
        }

        $this->assertSame(['USD' => 'USD', 'EUR' => 'EUR', 'UAH' => 'UAH'], $labels);
    }
}
