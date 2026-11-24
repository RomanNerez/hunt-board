<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages;

use App\Containers\AppSection\Vacancy\Actions\CreateVacancyNoteAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas\VacancyNoteForm;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Tables\VacancyNotesTable;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * @extends ManageRelatedRecords<Vacancy>
 */
final class ManageVacancyNotes extends ManageRelatedRecords
{
    /**
     * @var string
     */
    protected static string $resource = VacancyResource::class;

    /**
     * @var string
     */
    protected static string $relationship = 'notes';

    /**
     * @var string|null
     */
    protected static string|null $relationshipTitle = 'Нотатки';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Нотатки';

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return "Нотатки: {$this->getOwnerRecord()->title}";
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            ...$this->getResourceBreadcrumbs(),
            VacancyResource::getUrl('view', ['record' => $this->getOwnerRecord()]) => $this->getOwnerRecord()->title,
            $this->getBreadcrumb(),
        ];
    }

    /**
     * @return Vacancy
     */
    public function getOwnerRecord(): Vacancy
    {
        /** @var Vacancy $record */
        $record = $this->getRecord();

        return $record;
    }

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return VacancyNoteForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return VacancyNotesTable::configure($table)
            ->emptyStateActions([
                $this->createNoteAction(),
            ]);
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->createNoteAction(),
        ];
    }

    /**
     * @return CreateAction
     */
    private function createNoteAction(): CreateAction
    {
        return CreateAction::make()
            ->label('Додати нотатку')
            ->modalHeading('Додати нотатку')
            ->slideOver()
            ->createAnother(false)
            ->using(function (array $data): VacancyNote {
                /** @var array<string, mixed> $attributes */
                $attributes = $data;

                return app(CreateVacancyNoteAction::class)->run($this->getOwnerRecord()->id, $attributes);
            });
    }
}
