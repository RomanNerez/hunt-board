<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\SyncProfileLinksAction;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileLinksTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncProfileLinksAction::class)]
#[CoversClass(ReplaceProfileLinksTask::class)]
final class SyncProfileLinksActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testAddsLinksInGivenOrder(): void
    {
        $user = User::factory()->createOne();

        $links = app(SyncProfileLinksAction::class)->run($user->id, [
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
            ['label' => 'GitHub', 'url' => 'https://github.com/roman'],
        ]);

        $this->assertSame(['LinkedIn', 'GitHub'], $links->pluck('label')->all());
        $this->assertSame([['LinkedIn', 0], ['GitHub', 1]], $this->storedLinks($user->id));
    }

    /**
     * @return void
     */
    public function testRemovesMissingAndReordersRemainingLinks(): void
    {
        $user = User::factory()->createOne();
        $action = app(SyncProfileLinksAction::class);
        $action->run($user->id, [
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
            ['label' => 'GitHub', 'url' => 'https://github.com/roman'],
            ['label' => 'Блог', 'url' => 'https://roman.dev'],
        ]);

        $action->run($user->id, [
            ['label' => 'Блог', 'url' => 'https://roman.dev'],
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
        ]);

        $this->assertSame([['Блог', 0], ['LinkedIn', 1]], $this->storedLinks($user->id));
    }

    /**
     * @return void
     */
    public function testEmptyListClearsOnlyOwnLinks(): void
    {
        $user = User::factory()->createOne();
        ProfileLink::factory()->count(2)->create(['user_id' => $user->id]);
        $foreign = ProfileLink::factory()->createOne(['label' => 'GitHub']);

        $links = app(SyncProfileLinksAction::class)->run($user->id, []);

        $this->assertCount(0, $links);
        $this->assertSame([], $this->storedLinks($user->id));
        $this->assertModelExists($foreign);
    }

    /**
     * @return void
     */
    public function testStoresBothLabelsAndUrlsAndDropsBlankEnglish(): void
    {
        $user = User::factory()->createOne();

        app(SyncProfileLinksAction::class)->run($user->id, [
            ['label' => ['uk' => 'Портфоліо', 'en' => 'Portfolio'], 'url' => ['uk' => 'https://example.com/uk', 'en' => 'https://example.com/en']],
            ['label' => ['uk' => ' GitHub ', 'en' => ''], 'url' => ['uk' => ' https://github.com/roman ', 'en' => '']],
        ]);

        $links = ProfileLink::query()->where('user_id', $user->id)->orderBy('sort')->get();

        $this->assertEquals(['uk' => 'Портфоліо', 'en' => 'Portfolio'], $links[0]->getTranslations('label'));
        $this->assertSame(['uk' => 'GitHub'], $links[1]->getTranslations('label'));
        $this->assertEquals(['uk' => 'https://example.com/uk', 'en' => 'https://example.com/en'], $links[0]->getTranslations('url'));
        $this->assertSame(['uk' => 'https://github.com/roman'], $links[1]->getTranslations('url'));
    }

    /**
     * @param int $userId
     *
     * @return array<int, array{string, int}>
     */
    private function storedLinks(int $userId): array
    {
        return ProfileLink::query()
            ->where('user_id', $userId)
            ->orderBy('sort')
            ->get()
            ->map(static fn (ProfileLink $link): array => [$link->label, $link->sort])
            ->values()
            ->all();
    }
}
