<?php

namespace App\Containers\AppSection\Status\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\HtmlString;

enum StatusColor: string implements HasLabel
{
    case GRAY = 'gray';
    case RED = 'red';
    case ORANGE = 'orange';
    case AMBER = 'amber';
    case LIME = 'lime';
    case GREEN = 'green';
    case TEAL = 'teal';
    case CYAN = 'cyan';
    case BLUE = 'blue';
    case INDIGO = 'indigo';
    case VIOLET = 'violet';
    case PINK = 'pink';

    private const DARK_BACKGROUND_ALPHA = '26';

    private const DARK_BORDER_ALPHA = '4D';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::GRAY => 'Сірий',
            self::RED => 'Червоний',
            self::ORANGE => 'Помаранчевий',
            self::AMBER => 'Бурштиновий',
            self::LIME => 'Лаймовий',
            self::GREEN => 'Зелений',
            self::TEAL => 'Бірюзовий',
            self::CYAN => 'Блакитний',
            self::BLUE => 'Синій',
            self::INDIGO => 'Індиго',
            self::VIOLET => 'Фіолетовий',
            self::PINK => 'Рожевий',
        };
    }

    /**
     * @return string
     */
    public function lightBackground(): string
    {
        return $this->shades()[50];
    }

    /**
     * @return string
     */
    public function lightBorder(): string
    {
        return $this->shades()[200];
    }

    /**
     * @return string
     */
    public function lightText(): string
    {
        return $this->shades()[700];
    }

    /**
     * @return string
     */
    public function darkBackground(): string
    {
        return $this->shades()[500] . self::DARK_BACKGROUND_ALPHA;
    }

    /**
     * @return string
     */
    public function darkBorder(): string
    {
        return $this->shades()[500] . self::DARK_BORDER_ALPHA;
    }

    /**
     * @return string
     */
    public function darkText(): string
    {
        return $this->shades()[300];
    }

    /**
     * @param string $name
     *
     * @return HtmlString
     */
    public function renderBadge(string $name): HtmlString
    {
        return new HtmlString(view('appSection@status::badge', ['name' => $name, 'color' => $this])->render());
    }

    /**
     * @return array{50: string, 200: string, 300: string, 500: string, 700: string}
     */
    private function shades(): array
    {
        return match ($this) {
            self::GRAY => [50 => '#F9FAFB', 200 => '#E5E7EB', 300 => '#D1D5DB', 500 => '#6B7280', 700 => '#374151'],
            self::RED => [50 => '#FEF2F2', 200 => '#FECACA', 300 => '#FCA5A5', 500 => '#EF4444', 700 => '#B91C1C'],
            self::ORANGE => [50 => '#FFF7ED', 200 => '#FED7AA', 300 => '#FDBA74', 500 => '#F97316', 700 => '#C2410C'],
            self::AMBER => [50 => '#FFFBEB', 200 => '#FDE68A', 300 => '#FCD34D', 500 => '#F59E0B', 700 => '#B45309'],
            self::LIME => [50 => '#F7FEE7', 200 => '#D9F99D', 300 => '#BEF264', 500 => '#84CC16', 700 => '#4D7C0F'],
            self::GREEN => [50 => '#F0FDF4', 200 => '#BBF7D0', 300 => '#86EFAC', 500 => '#22C55E', 700 => '#15803D'],
            self::TEAL => [50 => '#F0FDFA', 200 => '#99F6E4', 300 => '#5EEAD4', 500 => '#14B8A6', 700 => '#0F766E'],
            self::CYAN => [50 => '#ECFEFF', 200 => '#A5F3FC', 300 => '#67E8F9', 500 => '#06B6D4', 700 => '#0E7490'],
            self::BLUE => [50 => '#EFF6FF', 200 => '#BFDBFE', 300 => '#93C5FD', 500 => '#3B82F6', 700 => '#1D4ED8'],
            self::INDIGO => [50 => '#EEF2FF', 200 => '#C7D2FE', 300 => '#A5B4FC', 500 => '#6366F1', 700 => '#4338CA'],
            self::VIOLET => [50 => '#F5F3FF', 200 => '#DDD6FE', 300 => '#C4B5FD', 500 => '#8B5CF6', 700 => '#6D28D9'],
            self::PINK => [50 => '#FDF2F8', 200 => '#FBCFE8', 300 => '#F9A8D4', 500 => '#EC4899', 700 => '#BE185D'],
        };
    }
}
