<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Models;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileWorkExperience::class)]
final class ProfileWorkExperienceTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testProjectsBelongToRecordFromNewestStart(): void
    {
        $experience = ProfileWorkExperience::factory()->createOne(['started_on' => '2019-09-03', 'finished_on' => '2020-08-21']);
        ProfileProject::factory()->createOne(['work_experience_id' => $experience->id, 'name' => 'Особистий кабінет клієнта', 'started_on' => '2019-09-03']);
        ProfileProject::factory()->createOne(['work_experience_id' => $experience->id, 'name' => 'Промо-лендинг', 'started_on' => '2020-03-01']);
        ProfileProject::factory()->createOne(['work_experience_id' => $experience->id, 'name' => 'CRM', 'started_on' => '2019-09-03']);
        ProfileProject::factory()->createOne(['name' => 'Чужий']);

        $this->assertSame(['Промо-лендинг', 'CRM', 'Особистий кабінет клієнта'], $experience->projects()->pluck('name')->all());
    }
}
