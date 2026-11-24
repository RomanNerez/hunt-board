<?php

namespace App\Containers\AppSection\Resume\Support;

final class ResumeLinkIcon
{
    /**
     * @return array<string, string>
     */
    public static function generic(): array
    {
        return [
            'heroicon-o-envelope' => 'Пошта',
            'heroicon-o-phone' => 'Телефон',
            'heroicon-o-globe-alt' => 'Сайт',
            'heroicon-o-link' => 'Посилання',
            'heroicon-o-map-pin' => 'Локація',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function brands(): array
    {
        return [
            'fab-github' => 'GitHub',
            'fab-linkedin' => 'LinkedIn',
            'fab-telegram' => 'Telegram',
            'fab-whatsapp' => 'WhatsApp',
            'fab-x-twitter' => 'X',
            'fab-facebook' => 'Facebook',
            'fab-instagram' => 'Instagram',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [...self::generic(), ...self::brands()];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function grouped(): array
    {
        return [
            'Іконки' => self::generic(),
            'Бренди' => self::brands(),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function groupedWithPreview(): array
    {
        return [
            'Іконки' => self::withPreview(self::generic()),
            'Бренди' => self::withPreview(self::brands()),
        ];
    }

    /**
     * @param array<string, string> $icons
     *
     * @return array<string, string>
     */
    private static function withPreview(array $icons): array
    {
        $options = [];

        foreach ($icons as $key => $name) {
            $options[$key] = '<span style="display: flex; align-items: center; gap: 0.5rem;">'
                . svg($key, '', ['style' => 'width: 1em; height: 1em; flex-shrink: 0;'])->toHtml()
                . '<span>' . e($name) . '</span></span>';
        }

        return $options;
    }
}
