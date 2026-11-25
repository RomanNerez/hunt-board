<?php

namespace App\Containers\AppSection\Resume\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResumeEmploymentType: string implements HasLabel
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Freelance = 'freelance';
    case Internship = 'internship';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::FullTime => 'Повна зайнятість',
            self::PartTime => 'Часткова зайнятість',
            self::Contract => 'Контракт',
            self::Freelance => 'Фріланс',
            self::Internship => 'Стажування',
        };
    }

    /**
     * @param ResumeLocale $locale
     *
     * @return string
     */
    public function pdfLabel(ResumeLocale $locale): string
    {
        return __("appSection@resume::pdf.employment_types.{$this->value}", locale: $locale->value);
    }
}
