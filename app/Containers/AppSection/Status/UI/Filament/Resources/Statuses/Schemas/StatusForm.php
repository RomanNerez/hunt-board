<?php

namespace App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Schemas;

use App\Containers\AppSection\Status\Enums\StatusColor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Enum;

final class StatusForm
{
    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(debounce: 300),
                ToggleButtons::make('color')
                    ->label('Колір')
                    ->options(self::colorOptions())
                    ->enum(StatusColor::class)
                    ->rule(new Enum(StatusColor::class))
                    ->required()
                    ->inline()
                    ->colors(array_fill_keys(array_column(StatusColor::cases(), 'value'), 'gray'))
                    ->live(),
                Html::make(static fn (Get $get): HtmlString => self::preview($get('name'), $get('color')))
                    ->key('preview'),
            ])
            ->columns(1);
    }

    /**
     * @return array<string, HtmlString>
     */
    private static function colorOptions(): array
    {
        $options = [];

        foreach (StatusColor::cases() as $color) {
            $options[$color->value] = $color->renderBadge($color->getLabel());
        }

        return $options;
    }

    /**
     * @param mixed $name
     * @param mixed $color
     *
     * @return HtmlString
     */
    private static function preview(mixed $name, mixed $color): HtmlString
    {
        $statusColor = $color instanceof StatusColor ? $color : StatusColor::tryFrom(is_string($color) ? $color : '');

        if (null === $statusColor) {
            return new HtmlString('');
        }

        return $statusColor->renderBadge(is_string($name) && '' !== $name ? $name : 'Приклад');
    }
}
