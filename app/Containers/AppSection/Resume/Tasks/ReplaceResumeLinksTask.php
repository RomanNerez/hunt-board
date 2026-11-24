<?php

namespace App\Containers\AppSection\Resume\Tasks;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ReplaceResumeLinksTask extends ParentTask
{
    /**
     * @param Resume $resume
     * @param list<array<string, mixed>> $links
     *
     * @return void
     */
    public function run(Resume $resume, array $links): void
    {
        $resume->links()->delete();

        foreach ($links as $position => $link) {
            $resume->links()->create([
                'icon' => $this->icon($link),
                'label' => $link['label'],
                'url' => $link['url'],
                'sort_order' => $position,
                'is_visible' => (bool) ($link['is_visible'] ?? true),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $link
     *
     * @return string|null
     */
    private function icon(array $link): string|null
    {
        $icon = $link['icon'] ?? null;

        return is_string($icon) && '' !== $icon ? $icon : null;
    }
}
