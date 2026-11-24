<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ReplaceResumeLanguagesTask extends ParentTask
{
    /**
     * @param Resume $resume
     * @param list<array<string, mixed>> $languages
     *
     * @return void
     */
    public function run(Resume $resume, array $languages): void
    {
        $resume->languages()->delete();

        foreach ($languages as $position => $language) {
            $resume->languages()->create([
                'language' => $language['language'],
                'level' => $this->text($language, 'level'),
                'level_label' => $this->text($language, 'level_label'),
                'sort_order' => $position,
                'is_visible' => (bool) ($language['is_visible'] ?? true),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $language
     * @param string $key
     *
     * @return string|null
     */
    private function text(array $language, string $key): string|null
    {
        $value = $language[$key] ?? null;

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return is_string($value) && '' !== $value ? $value : null;
    }
}
