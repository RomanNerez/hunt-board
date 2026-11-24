<?php

namespace App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages\ListVacancies;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages\ViewVacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas\VacancyForm;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas\VacancyInfolist;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Tables\VacanciesTable;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class VacancyResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Vacancy::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 10;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'title';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'Вакансія';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Вакансії';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Вакансії';

    /**
     * @var bool
     */
    protected static bool $hasTitleCaseModelLabel = false;

    /**
     * @var SubNavigationPosition|null
     */
    protected static SubNavigationPosition|null $subNavigationPosition = SubNavigationPosition::Top;

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return VacancyForm::configure($schema);
    }

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function infolist(Schema $schema): Schema
    {
        return VacancyInfolist::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return VacanciesTable::configure($table);
    }

    /**
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(Vacancy::DISPLAY_RELATIONS);
    }

    /**
     * @param int|string $key
     * @param \Closure|null $modifyQuery
     *
     * @return Model|null
     */
    public static function resolveRecordRouteBinding(int|string $key, \Closure|null $modifyQuery = null): Model|null
    {
        if (!ctype_digit((string) $key)) {
            return null;
        }

        return parent::resolveRecordRouteBinding($key, $modifyQuery);
    }

    /**
     * @param Page $page
     *
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewVacancy::class,
        ]);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListVacancies::route('/'),
            'view' => ViewVacancy::route('/{record}'),
        ];
    }
}
