<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Tasks;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\CreateStackTask;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateStackTask::class)]
final class CreateStackTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesStack(): void
    {
        $record = app(CreateStackTask::class)->run(['name' => 'Laravel']);

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'Laravel']);
    }

    /**
     * @return void
     */
    public function testTrimsName(): void
    {
        $record = app(CreateStackTask::class)->run(['name' => ' Docker ']);

        $this->assertSame('Docker', $record->name);
    }

    /**
     * @return void
     */
    public function testRejectsEmptyName(): void
    {
        $this->assertNameRejected(['name' => '   ']);
    }

    /**
     * @return void
     */
    public function testRejectsTooLongName(): void
    {
        $this->assertNameRejected(['name' => str_repeat('a', 101)]);
    }

    /**
     * @return void
     */
    public function testRejectsNameDifferingOnlyByCase(): void
    {
        Stack::factory()->createOne(['name' => 'PostgreSQL']);

        $this->assertNameRejected(['name' => 'postgresql']);
        $this->assertDatabaseCount(Stack::class, 1);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    private function assertNameRejected(array $data): void
    {
        try {
            app(CreateStackTask::class)->run($data);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }
    }
}
