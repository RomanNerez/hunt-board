<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Vacancy\Actions\CreateVacancyAction;
use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateVacancyAction::class)]
final class CreateVacancyActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesVacancyWithRequiredAttributesOnly(): void
    {
        $macPaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $applied = Status::factory()->createOne(['name' => 'Applied']);

        $vacancy = app(CreateVacancyAction::class)->run([
            'company_id' => $macPaw->id,
            'status_id' => $applied->id,
            'title' => 'Senior PHP Developer',
        ]);

        $this->assertSame('Senior PHP Developer', $vacancy->title);
        $this->assertTrue($vacancy->relationLoaded('company'));
        $this->assertTrue($vacancy->relationLoaded('status'));
        $this->assertTrue($vacancy->relationLoaded('vacancySources'));
        $this->assertSame('MacPaw', $vacancy->company->name);
        $this->assertSame('Applied', $vacancy->status->name);
        $this->assertCount(0, $vacancy->vacancySources);
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'description' => null,
            'work_format' => null,
            'salary_min' => null,
            'currency' => null,
            'applied_at' => null,
            'notes' => null,
        ]);
    }

    /**
     * @return void
     */
    public function testCreatesVacancyWithAllAttributes(): void
    {
        $macPaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);

        $vacancy = app(CreateVacancyAction::class)->run([
            'company_id' => $macPaw->id,
            'status_id' => $applied->id,
            'title' => 'Senior PHP Developer',
            'sources' => [['vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/companies/macpaw/vacancies/123/']],
            'description' => "Про проєкт.\n\nВимоги.",
            'location' => 'Київ',
            'work_format' => WorkFormat::HYBRID,
            'salary_min' => 4000,
            'salary_max' => 5500,
            'currency' => Currency::USD,
            'applied_at' => '2026-11-20',
            'next_action_at' => '2026-11-25 10:00:00',
            'notes' => 'Рекрутерка — Олена',
        ]);

        $vacancy->refresh();

        $this->assertSame(['DOU'], $vacancy->vacancySources->pluck('name')->all());
        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id, 'vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/companies/macpaw/vacancies/123/']);
        $this->assertSame("Про проєкт.\n\nВимоги.", $vacancy->description);
        $this->assertSame('Київ', $vacancy->location);
        $this->assertSame(WorkFormat::HYBRID, $vacancy->work_format);
        $this->assertSame(4000, $vacancy->salary_min);
        $this->assertSame(5500, $vacancy->salary_max);
        $this->assertSame(Currency::USD, $vacancy->currency);
        $this->assertSame('2026-11-20', $vacancy->applied_at?->toDateString());
        $this->assertSame('2026-11-25 10:00:00', $vacancy->next_action_at?->toDateTimeString());
        $this->assertSame('Рекрутерка — Олена', $vacancy->notes);
    }
}
