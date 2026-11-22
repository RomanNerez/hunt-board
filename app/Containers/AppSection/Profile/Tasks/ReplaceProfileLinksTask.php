<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileLinkRepository;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

final class ReplaceProfileLinksTask extends ParentTask
{
    /**
     * @var list<string>
     */
    private const ATTRIBUTES = ['label', 'url'];

    /**
     * @param ProfileLinkRepository<ProfileLink> $repository
     */
    public function __construct(
        private readonly ProfileLinkRepository $repository,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileLink>
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
