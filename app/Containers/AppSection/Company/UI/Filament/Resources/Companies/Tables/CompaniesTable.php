<?php

namespace App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Tables;

use App\Containers\AppSection\Company\Actions\DeleteCompanyAction;
use App\Containers\AppSection\Company\Actions\UpdateCompanyAction;
use App\Containers\AppSection\Company\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CompaniesTable
{
    public const LOGO_PLACEHOLDER = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjOWNhM2FmIiBzdHJva2Utd2lkdGg9IjEuNSI+PHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiBmaWxsPSIjZjNmNGY2IiBzdHJva2U9Im5vbmUiLz48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGQ9Ik02IDE4VjZoN3YxMk0xMyAxMGg1djhNNSAxOGgxNE04LjUgOWgyTTguNSAxMmgyTTguNSAxNWgyIi8+PC9zdmc+';

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with(['companyType', 'domains', 'media']))
            ->columns([
                ImageColumn::make('logo')
                    ->label('Логотип')
                    ->getStateUsing(static fn (Company $record): string|null => $record->logoThumbPath())
                    ->disk('public')
                    ->checkFileExistence(false)
                    ->defaultImageUrl(self::LOGO_PLACEHOLDER)
                    ->square()
                    ->extraImgAttributes([
                        'style' => 'object-fit: contain; padding: 2px; background-color: #fff; border: 1px solid var(--gray-200); border-radius: 0.375rem;',
                    ]),
                TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('companyType.name')
                    ->label('Тип'),
                TextColumn::make('domains.name')
                    ->label('Домени')
                    ->badge(),
                TextColumn::make('website_url')
                    ->label('Сайт')
                    ->url(static fn (Company $record): string|null => $record->website_url)
                    ->openUrlInNewTab()
                    ->toggleable(),
                TextColumn::make('vacancies_url')
                    ->label('Вакансії')
                    ->url(static fn (Company $record): string|null => $record->vacancies_url)
                    ->openUrlInNewTab()
                    ->limit(40)
                    ->tooltip(static fn (Company $record): string|null => $record->vacancies_url)
                    ->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('company_type_id')
                    ->label('Тип компанії')
                    ->relationship('companyType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('domains')
                    ->label('Домен')
                    ->relationship('domains', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->mutateRecordDataUsing(static function (array $data, Company $record): array {
                        $data['domains'] = $record->domains->pluck('id')->all();
                        $data['logo'] = $record->getFirstMedia(Company::LOGO_COLLECTION)?->getPathRelativeToRoot();

                        return $data;
                    })
                    ->using(static function (Company $record, array $data): Company {
                        /** @var array<string, mixed> $attributes */
                        $attributes = $data;

                        return app(UpdateCompanyAction::class)->run($record->id, $attributes);
                    }),
                DeleteAction::make()
                    ->using(static fn (Company $record): bool => app(DeleteCompanyAction::class)->run($record->id)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Видалити вибрані компанії')
                        ->using(static function (Collection $records): void {
                            /** @var Company $record */
                            foreach ($records as $record) {
                                app(DeleteCompanyAction::class)->run($record->id);
                            }
                        }),
                ]),
            ]);
    }
}
