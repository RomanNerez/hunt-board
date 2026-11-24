<?php

namespace App\Containers\AppSection\Contact\Actions;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Contact\Tasks\CreateContactTask;
use App\Containers\AppSection\Contact\Tasks\ReplaceContactSourcesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreateContactAction extends ParentAction
{
    /**
     * @param CreateContactTask $createContactTask
     * @param ReplaceContactSourcesTask $replaceContactSourcesTask
     */
    public function __construct(
        private readonly CreateContactTask $createContactTask,
        private readonly ReplaceContactSourcesTask $replaceContactSourcesTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Contact
     *
     * @throws \Throwable
     */
    public function run(array $data): Contact
    {
        /** @var list<array<string, mixed>> $sources */
        $sources = Arr::pull($data, 'sources') ?? [];

        return DB::transaction(function () use ($data, $sources): Contact {
            $contact = $this->createContactTask->run($data);

            $this->replaceContactSourcesTask->run($contact, $sources);

            return $contact->load('vacancySources');
        });
    }
}
