<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeLinks;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditResumeLinks::class)]
final class EditResumeLinksTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
        $this->actingAs($this->user);
    }

    /**
     * @return void
     */
    public function testFillsExistingLinksInSavedOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'GitHub', 'icon' => 'fab-github', 'sort_order' => 1]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'LinkedIn', 'sort_order' => 0, 'is_visible' => false]);

        $rows = array_values(Livewire::test(EditResumeLinks::class, ['record' => $resume->id])->get('data.links'));

        $this->assertSame(['LinkedIn', 'GitHub'], array_column($rows, 'label'));
        $this->assertSame('fab-github', $rows[1]['icon']);
        $this->assertFalse($rows[0]['is_visible']);
    }

    /**
     * @return void
     */
    public function testSavesReorderedAndChangedLinksAndStaysOnPage(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id, 'headline' => 'Не чіпати']);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'LinkedIn', 'sort_order' => 0]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'GitHub', 'sort_order' => 1]);

        Livewire::test(EditResumeLinks::class, ['record' => $resume->id])
            ->fillForm(['links' => [
                ['icon' => 'fab-github', 'label' => 'GitHub', 'url' => 'https://github.com/john', 'is_visible' => true],
                ['icon' => null, 'label' => 'Пошта', 'url' => 'mailto:john@mail.com', 'is_visible' => false],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNoRedirect();

        $links = $resume->links()->get();

        $this->assertSame(['GitHub', 'Пошта'], $links->pluck('label')->all());
        $this->assertSame('fab-github', $links[0]->icon);
        $this->assertFalse($links[1]->is_visible);
        $this->assertSame('Не чіпати', $resume->refresh()->headline);
    }

    /**
     * @param array<string, mixed> $link
     * @param string $field
     *
     * @return void
     */
    #[DataProvider('invalidLinks')]
    public function testRejectsInvalidLinks(array $link, string $field): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResumeLinks::class, ['record' => $resume->id])
            ->fillForm(['links' => [$link]])
            ->call('save')
            ->assertHasFormErrors(["links.0.$field"]);

        $this->assertSame(0, ResumeLink::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidLinks(): array
    {
        return [
            'без підпису' => [['label' => '', 'url' => 'https://a.com'], 'label'],
            'без адреси' => [['label' => 'A', 'url' => ''], 'url'],
            'javascript' => [['label' => 'A', 'url' => 'javascript:alert(1)'], 'url'],
            'без схеми' => [['label' => 'A', 'url' => 'linkedin.com/in/john'], 'url'],
            'довгий підпис' => [['label' => str_repeat('a', 256), 'url' => 'https://a.com'], 'label'],
            'довга адреса' => [['label' => 'A', 'url' => 'https://a.com/' . str_repeat('a', 2048)], 'url'],
        ];
    }

    /**
     * @param string $url
     *
     * @return void
     */
    #[DataProvider('allowedUrls')]
    public function testAcceptsAllowedSchemes(string $url): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResumeLinks::class, ['record' => $resume->id])
            ->fillForm(['links' => [['label' => 'A', 'url' => $url]]])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function allowedUrls(): array
    {
        return [
            'https' => ['https://a.com'],
            'http' => ['http://a.com'],
            'mailto' => ['mailto:a@a.com'],
            'tel' => ['tel:+380501234567'],
        ];
    }
}
