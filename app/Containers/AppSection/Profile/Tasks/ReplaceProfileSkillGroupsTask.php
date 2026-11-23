<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileSkillGroupRepository;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tasks\Concerns\NormalizesTranslations;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

final class ReplaceProfileSkillGroupsTask extends ParentTask
{
    use NormalizesTranslations;

    /**
     * @param ProfileSkillGroupRepository<ProfileSkillGroup> $repository
     */
    public function __construct(
        private readonly ProfileSkillGroupRepository $repository,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileSkillGroup>
     *
     * @throws ResourceCreationFailed
     */
    public function run(int $userId, array $items): Collection
    {
        ProfileSkillGroup::detachStacksFrom(ProfileSkillGroup::query()->where('user_id', $userId));
        $this->repository->deleteWhere(['user_id' => $userId]);

        $records = new Collection();

        foreach ($items as $sort => $item) {
            $records->push($this->repository->create([
                'user_id' => $userId,
                'name' => $this->normalizeTranslation($item['name'] ?? null),
                'sort' => $sort,
            ]));
        }

        return $records;
    }
}
