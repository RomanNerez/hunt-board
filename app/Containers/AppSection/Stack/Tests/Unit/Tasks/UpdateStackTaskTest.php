<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Tasks;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\UpdateStackTask;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateStackTask::class)]
final class UpdateStackTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testRenamesStack(): void
    {
        $record = Stack::factory()->createOne(['name' => 'VueJS']);

        app(UpdateStackTask::class)->run($record->id, ['name' => ' Vue.js ']);

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'Vue.js']);
    }

    /**
     * @return void
     */
    public function testAllowsChangingCaseOfOwnName(): void
    {
        $record = Stack::factory()->createOne(['name' => 'mysql']);

        app(UpdateStackTask::class)->run($record->id, ['name' => 'MySQL']);

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'MySQL']);
    }

    /**
     * @return void
     */
    public function testRejectsNameOfAnotherStackIgnoringCase(): void
    {
        Stack::factory()->createOne(['name' => 'PostgreSQL']);
        $record = Stack::factory()->createOne(['name' => 'MySQL']);

        try {
            app(UpdateStackTask::class)->run($record->id, ['name' => 'POSTGRESQL']);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'MySQL']);
    }
}
