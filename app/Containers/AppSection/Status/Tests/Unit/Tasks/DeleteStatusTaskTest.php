<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Tasks;

use App\Containers\AppSection\Status\Exceptions\StatusInUseException;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tasks\DeleteStatusTask;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteStatusTask::class)]
final class DeleteStatusTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesStatus(): void
    {
        $status = Status::factory()->createOne();

        $result = app(DeleteStatusTask::class)->run($status->id);

        $this->assertTrue($result);
        $this->assertModelMissing($status);
    }

    /**
     * @return void
     */
    public function testStatusUsedByVacancyIsKeptAndRejected(): void
    {
        $status = Status::factory()->createOne();
        Vacancy::factory()->createOne(['status_id' => $status->id]);

        try {
            DB::transaction(static fn (): bool => app(DeleteStatusTask::class)->run($status->id));
            $this->fail('Deleting a status used by a vacancy must throw.');
        } catch (StatusInUseException $exception) {
            $this->assertSame('Статус використовується вакансіями.', $exception->getMessage());
        }

        $this->assertModelExists($status);
    }
}
