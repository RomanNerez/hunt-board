<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Data;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testResumesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('resumes', [
            'id' => 'int8',
            'user_id' => 'int8',
            'kind' => 'varchar',
            'first_name' => 'varchar',
            'last_name' => 'varchar',
            'headline' => 'varchar',
            'summary' => 'text',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testDeletingUserDeletesItsResumes(): void
    {
        $user = User::factory()->createOne();
        $resumes = Resume::factory()->count(2)->createMany([['user_id' => $user->id], ['user_id' => $user->id]]);
        $otherResume = Resume::factory()->createOne();

        $user->delete();

        foreach ($resumes as $resume) {
            $this->assertModelMissing($resume);
        }

        $this->assertModelExists($otherResume);
    }

    /**
     * @return void
     */
    public function testResumeLinksTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('resume_links', [
            'id' => 'int8',
            'resume_id' => 'int8',
            'icon' => 'varchar',
            'label' => 'varchar',
            'url' => 'varchar',
            'sort_order' => 'int4',
            'is_visible' => 'bool',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testLinksAreOrderedAndDeletedWithResume(): void
    {
        $resume = Resume::factory()->createOne();
        $second = ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 1]);
        $first = ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 0]);

        $this->assertSame([$first->id, $second->id], $resume->links()->pluck('id')->all());

        $resume->delete();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
    }

    /**
     * @return void
     */
    public function testResumeExperiencesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('resume_experiences', [
            'id' => 'int8',
            'resume_id' => 'int8',
            'company' => 'varchar',
            'company_url' => 'varchar',
            'position' => 'varchar',
            'location' => 'varchar',
            'employment_type' => 'varchar',
            'start_date' => 'date',
            'end_date' => 'date',
            'description' => 'text',
            'sort_order' => 'int4',
            'is_visible' => 'bool',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testExperiencesAreOrderedAndDeletedWithResume(): void
    {
        $resume = Resume::factory()->createOne();
        $second = ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 1]);
        $first = ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 0]);

        $this->assertSame([$first->id, $second->id], $resume->experiences()->pluck('id')->all());

        $resume->delete();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
    }

    /**
     * @return void
     */
    public function testResumeEducationsTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('resume_educations', [
            'id' => 'int8',
            'resume_id' => 'int8',
            'institution' => 'varchar',
            'institution_url' => 'varchar',
            'degree' => 'varchar',
            'field_of_study' => 'varchar',
            'location' => 'varchar',
            'start_date' => 'date',
            'end_date' => 'date',
            'description' => 'text',
            'sort_order' => 'int4',
            'is_visible' => 'bool',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testEducationsAreOrderedAndDeletedWithResume(): void
    {
        $resume = Resume::factory()->createOne();
        $second = ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 1]);
        $first = ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 0]);

        $this->assertSame([$first->id, $second->id], $resume->educations()->pluck('id')->all());

        $resume->delete();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
    }

    /**
     * @return void
     */
    public function testResumeLanguagesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('resume_languages', [
            'id' => 'int8',
            'resume_id' => 'int8',
            'language' => 'varchar',
            'level' => 'varchar',
            'level_label' => 'varchar',
            'sort_order' => 'int4',
            'is_visible' => 'bool',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testLanguagesAreOrderedAndDeletedWithResume(): void
    {
        $resume = Resume::factory()->createOne();
        $second = ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 1]);
        $first = ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'sort_order' => 0]);

        $this->assertSame([$first->id, $second->id], $resume->languages()->pluck('id')->all());

        $resume->delete();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
    }

    /**
     * @return void
     */
    public function testLanguageIsUniquePerResumeButNotAcrossResumes(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Англійська']);

        ResumeLanguage::factory()->createOne(['language' => 'Англійська']);

        $this->expectException(UniqueConstraintViolationException::class);

        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Англійська']);
    }
}
