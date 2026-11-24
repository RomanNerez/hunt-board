<?php

namespace App\Containers\AppSection\Vacancy\Models\Concerns;

use Illuminate\Support\Str;

trait SanitizesRichText
{
    /**
     * @param string|null $value
     *
     * @return string|null
     */
    public static function sanitizeRichText(string|null $value): string|null
    {
        if (null === $value) {
            return null;
        }

        $html = trim(Str::sanitizeHtml($value));
        $visibleText = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\u{A0}");

        return '' === $visibleText ? null : $html;
    }
}
