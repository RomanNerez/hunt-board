<?php

namespace App\Containers\AppSection\Profile\Tasks;

use App\Containers\AppSection\Profile\Data\Repositories\UserProfileRepository;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;
use Prettus\Validator\Exceptions\ValidatorException;

final class UpsertUserProfileTask extends ParentTask
{
    /**
     * @var list<string>
     */
    private const ATTRIBUTES = ['first_name', 'last_name', 'email', 'phone', 'location', 'headline', 'summary'];

    /**
     * @param UserProfileRepository<UserProfile> $repository
     */
    public function __construct(
        private readonly UserProfileRepository $repository,
    ) {
    }

    /**
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return UserProfile
     *
     * @throws ValidatorException
     */
    public function run(int $userId, array $data): UserProfile
    {
        /** @var UserProfile $profile */
        $profile = $this->repository->updateOrCreate(['user_id' => $userId], Arr::only($data, self::ATTRIBUTES));

        return $profile;
    }
}
