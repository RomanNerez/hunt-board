<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\DeleteCompanyLogoTask;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tasks\SyncCompanyDomainsTask;
use App\Containers\AppSection\Company\Tasks\UpdateCompanyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

final class UpdateCompanyAction extends ParentAction
{
    /**
     * @param UpdateCompanyTask $updateCompanyTask
     * @param SyncCompanyDomainsTask $syncCompanyDomainsTask
     * @param ReplaceCompanyLogoTask $replaceCompanyLogoTask
     * @param DeleteCompanyLogoTask $deleteCompanyLogoTask
     */
    public function __construct(
        private readonly UpdateCompanyTask $updateCompanyTask,
        private readonly SyncCompanyDomainsTask $syncCompanyDomainsTask,
        private readonly ReplaceCompanyLogoTask $replaceCompanyLogoTask,
        private readonly DeleteCompanyLogoTask $deleteCompanyLogoTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Company
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): Company
    {
        return DB::transaction(function () use ($id, $data): Company {
            /** @var array<string, mixed> $attributes */
            $attributes = Arr::except($data, ['domains', 'logo']);
            $company = $this->updateCompanyTask->run($id, $attributes);

            $this->persistRelations($company, $data);

            return $company->load(['companyType', 'domains', 'media']);
        });
    }

    /**
     * @param Company $company
     * @param array<string, mixed> $data
     *
     * @return void
     *
     * @throws FileCannotBeAdded
     */
    private function persistRelations(Company $company, array $data): void
    {
        if (array_key_exists('domains', $data)) {
            $this->syncCompanyDomainsTask->run($company, $this->domainIds($data['domains']));
        }

        if (!array_key_exists('logo', $data)) {
            return;
        }

        if ($data['logo'] instanceof UploadedFile) {
            $this->replaceCompanyLogoTask->run($company, $data['logo']);
        } elseif (null === $data['logo']) {
            $this->deleteCompanyLogoTask->run($company);
        }
    }

    /**
     * @param mixed $domains
     *
     * @return list<int>
     */
    private function domainIds(mixed $domains): array
    {
        $ids = [];

        foreach (is_array($domains) ? $domains : [] as $id) {
            if (is_numeric($id)) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}
