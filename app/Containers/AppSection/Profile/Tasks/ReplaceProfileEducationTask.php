<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileEducationRepository;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

final class ReplaceProfileEducationTask extends ParentTask
{
    /**
     * @var list<string>
     */
    private const ATTRIBUTES = ['type', 'title', 'institution', 'started_on', 'finished_on', 'url', 'description'];

    /**
     * @param ProfileEducationRepository<ProfileEducation> $repository
     */
    public function __construct(
        private readonly ProfileEducationRepository $repository,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileEducation>
     *
     * @throws ResourceCreationFailed
     */
    public function run(int $userId, array $items): Collection
    {
        $this->repository->deleteWhere(['user_id' => $userId]);

        $records = new Collection();

        foreach ($items as $sort => $item) {
            $records->push($this->repository->create([
                ...Arr::only($item, self::ATTRIBUTES),
                'user_id' => $userId,
                'sort' => $sort,
            ]));
        }

        return $records;
    }
}
