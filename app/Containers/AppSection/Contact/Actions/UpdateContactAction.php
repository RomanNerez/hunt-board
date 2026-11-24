<?php

namespace App\Containers\AppSection\Contact\Actions;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Contact\Tasks\ReplaceContactSourcesTask;
use App\Containers\AppSection\Contact\Tasks\UpdateContactTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class UpdateContactAction extends ParentAction
{
    /**
     * @param UpdateContactTask $updateContactTask
     * @param ReplaceContactSourcesTask $replaceContactSourcesTask
     */
    public function __construct(
        private readonly UpdateContactTask $updateContactTask,
        private readonly ReplaceContactSourcesTask $replaceContactSourcesTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Contact
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): Contact
    {
        $hasSources = array_key_exists('sources', $data);

        /** @var list<array<string, mixed>> $sources */
        $sources = Arr::pull($data, 'sources') ?? [];

        return DB::transaction(function () use ($id, $data, $hasSources, $sources): Contact {
            $contact = $this->updateContactTask->run($id, $data);

            if ($hasSources) {
                $this->replaceContactSourcesTask->run($contact, $sources);
            }

            return $contact->load('vacancySources');
        });
    }
}
