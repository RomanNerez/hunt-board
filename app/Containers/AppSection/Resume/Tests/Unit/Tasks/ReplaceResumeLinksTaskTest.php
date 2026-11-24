<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeLinksTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReplaceResumeLinksTask::class)]
final class ReplaceResumeLinksTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testReplacesListWithPositionAsSortOrderAndVisibleByDefault(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'Старе']);

        app(ReplaceResumeLinksTask::class)->run($resume, [
            ['icon' => 'fab-github', 'label' => 'GitHub', 'url' => 'https://github.com/john'],
            ['icon' => '', 'label' => 'Пошта', 'url' => 'mailto:john@mail.com', 'is_visible' => false],
        ]);

        $links = $resume->links()->get();

        $this->assertSame(['GitHub', 'Пошта'], $links->pluck('label')->all());
        $this->assertSame([0, 1], $links->pluck('sort_order')->all());
        $this->assertSame([true, false], $links->pluck('is_visible')->all());
        $this->assertSame(['fab-github', null], $links->pluck('icon')->all());
    }

    /**
     * @return void
     */
    public function testDoesNotTouchLinksOfOtherResumes(): void
    {
        $resume = Resume::factory()->createOne();
        $otherLink = ResumeLink::factory()->createOne();

        app(ReplaceResumeLinksTask::class)->run($resume, []);

        $this->assertModelExists($otherLink);
    }
}
