<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Profile\Actions\SyncProfileLanguagesAction;
use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileLanguagesTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncProfileLanguagesAction::class)]
#[CoversClass(ReplaceProfileLanguagesTask::class)]
final class SyncProfileLanguagesActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testStoresLanguagesInOrderAndChangesLevel(): void
    {
        $user = User::factory()->createOne();
        $action = app(SyncProfileLanguagesAction::class);
        $action->run($user->id, [
            ['language' => 'Українська', 'level' => LanguageLevel::NATIVE->value],
            ['language' => 'Англійська', 'level' => LanguageLevel::B2->value],
        ]);

        $action->run($user->id, [
            ['language' => 'Українська', 'level' => LanguageLevel::NATIVE->value],
            ['language' => 'Англійська', 'level' => LanguageLevel::C1->value],
        ]);

        $this->assertSame(
            [['Українська', LanguageLevel::NATIVE, 0], ['Англійська', LanguageLevel::C1, 1]],
            $this->storedLanguages($user->id),
        );
    }

    /**
     * @return void
     */
    public function testTrimsLanguageName(): void
    {
        $user = User::factory()->createOne();

        app(SyncProfileLanguagesAction::class)->run($user->id, [
            ['language' => ' Англійська ', 'level' => LanguageLevel::B2->value],
        ]);

        $this->assertSame([['Англійська', LanguageLevel::B2, 0]], $this->storedLanguages($user->id));
    }

    /**
     * @return void
     */
    public function testReplacesOnlyOwnLanguages(): void
    {
        $user = User::factory()->createOne();
        ProfileLanguage::factory()->createOne(['user_id' => $user->id]);
        $foreign = ProfileLanguage::factory()->createOne();

        app(SyncProfileLanguagesAction::class)->run($user->id, []);

        $this->assertSame([], $this->storedLanguages($user->id));
        $this->assertModelExists($foreign);
    }

    /**
     * @return void
     */
    public function testDuplicateInsertRollsBackAndKeepsPreviousList(): void
    {
        $user = User::factory()->createOne();
        $action = app(SyncProfileLanguagesAction::class);
        $action->run($user->id, [['language' => 'Польська', 'level' => LanguageLevel::A2->value]]);

        $this->assertThrows(
            static fn (): mixed => $action->run($user->id, [
                ['language' => 'Англійська', 'level' => LanguageLevel::B2->value],
                ['language' => 'Англійська ', 'level' => LanguageLevel::C1->value],
            ]),
            ResourceCreationFailed::class,
        );

        $this->assertSame([['Польська', LanguageLevel::A2, 0]], $this->storedLanguages($user->id));
    }

    /**
     * @param int $userId
     *
     * @return array<int, array{string, LanguageLevel, int}>
     */
    private function storedLanguages(int $userId): array
    {
        return ProfileLanguage::query()
            ->where('user_id', $userId)
            ->orderBy('sort')
            ->get()
            ->map(static fn (ProfileLanguage $language): array => [$language->language, $language->level, $language->sort])
            ->values()
            ->all();
    }
}
