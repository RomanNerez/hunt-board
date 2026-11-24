<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeEducationsTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReplaceResumeEducationsTask::class)]
final class ReplaceResumeEducationsTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testReplacesListWithPositionAsSortOrderAndNormalizedDates(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'Старе']);

        app(ReplaceResumeEducationsTask::class)->run($resume, [
            [
                'institution' => 'Braine Academy',
                'institution_url' => 'https://brainecollege.com',
                'degree' => 'Молодший спеціаліст',
                'field_of_study' => 'Програмування',
                'location' => 'Київ',
                'start_date' => '2016-09-17',
                'end_date' => '2020-06-20',
                'description' => 'Опис',
                'is_visible' => false,
            ],
            [
                'institution' => 'Prometheus',
                'institution_url' => '',
                'degree' => '',
                'field_of_study' => 'PHP',
                'location' => '',
                'start_date' => null,
                'end_date' => null,
                'description' => '',
            ],
        ]);

        $educations = $resume->educations()->get();

        $this->assertSame(['Braine Academy', 'Prometheus'], $educations->pluck('institution')->all());
        $this->assertSame([0, 1], $educations->pluck('sort_order')->all());
        $this->assertSame([false, true], $educations->pluck('is_visible')->all());
        $this->assertSame('2016-09-01', $educations[0]->start_date?->toDateString());
        $this->assertSame('2020-06-01', $educations[0]->end_date?->toDateString());
        $this->assertNull($educations[1]->start_date);
        $this->assertNull($educations[1]->end_date);
        $this->assertNull($educations[1]->institution_url);
        $this->assertNull($educations[1]->degree);
        $this->assertNull($educations[1]->location);
        $this->assertNull($educations[1]->description);
        $this->assertSame('PHP', $educations[1]->field_of_study);
    }

    /**
     * @return void
     */
    public function testDoesNotTouchEducationsOfOtherResumes(): void
    {
        $resume = Resume::factory()->createOne();
        $other = ResumeEducation::factory()->createOne();

        app(ReplaceResumeEducationsTask::class)->run($resume, []);

        $this->assertModelExists($other);
    }
}
