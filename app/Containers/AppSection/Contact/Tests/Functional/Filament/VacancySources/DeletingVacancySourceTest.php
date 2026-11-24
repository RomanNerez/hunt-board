<?php

namespace App\Containers\AppSection\Contact\Tests\Functional\Filament\VacancySources;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Contact\Tests\FunctionalTestCase;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Pages\ManageVacancySources;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class DeletingVacancySourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testDeletingSourceKeepsContactWithRemainingSources(): void
    {
        $this->actingAs(User::factory()->createOne());

        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $telegram = VacancySource::factory()->createOne(['name' => 'Telegram']);
        $contact = Contact::factory()
            ->hasAttached($linkedIn, ['url' => 'https://www.linkedin.com/in/olena-koval/'])
            ->hasAttached($telegram, ['url' => 'https://t.me/olena_koval'])
            ->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('delete')->table($telegram))
            ->assertNotified();

        $this->assertModelMissing($telegram);
        $this->assertModelExists($contact);
        $this->assertSame(['LinkedIn'], $contact->vacancySources()->pluck('name')->all());
        $this->assertDatabaseHas('contact_vacancy_source', ['contact_id' => $contact->id, 'vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/']);
    }
}
