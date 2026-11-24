<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Schemas;

use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Support\ResumeLinkIcon;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

final class ResumeLinksRepeater
{
    /**
     * @return Repeater
     */
    public static function make(): Repeater
    {
        return Repeater::make('links')
            ->label('Посилання')
            ->hiddenLabel()
            ->schema([
                Select::make('icon')
                    ->label('Іконка')
                    ->options(ResumeLinkIcon::groupedWithPreview())
                    ->allowHtml()
                    ->searchable(),
                TextInput::make('label')
                    ->label('Підпис')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('Адреса')
                    ->required()
                    ->startsWith(['http://', 'https://', 'mailto:', 'tel:'])
                    ->maxLength(2048),
                Toggle::make('is_visible')
                    ->label('Показувати')
                    ->default(true),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->reorderableWithDragAndDrop()
            ->addActionLabel('Додати посилання')
            ->columnSpanFull();
    }

    /**
     * @param Collection<int, ResumeLink> $links
     *
     * @return list<array{icon: string|null, label: string, url: string, is_visible: bool}>
     */
    public static function stateFrom(Collection $links): array
    {
        return array_values($links
            ->map(static fn (ResumeLink $link): array => [
                'icon' => $link->icon,
                'label' => $link->label,
                'url' => $link->url,
                'is_visible' => $link->is_visible,
            ])
            ->all());
    }
}
