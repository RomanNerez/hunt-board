<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\CreateCompanyTask;
use App\Containers\AppSection\Company\Tasks\DeleteCompanyLogoTask;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tasks\SyncCompanyDomainsTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

final class CreateCompanyAction extends ParentAction
{
    /**
     * @param CreateCompanyTask $createCompanyTask
     * @param SyncCompanyDomainsTask $syncCompanyDomainsTask
     * @param ReplaceCompanyLogoTask $replaceCompanyLogoTask
     * @param DeleteCompanyLogoTask $deleteCompanyLogoTask
     */
    public function __construct(
        private readonly CreateCompanyTask $createCompanyTask,
        private readonly SyncCompanyDomainsTask $syncCompanyDomainsTask,
        private readonly ReplaceCompanyLogoTask $replaceCompanyLogoTask,
        private readonly DeleteCompanyLogoTask $deleteCompanyLogoTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Company
     *
     * @throws \Throwable
     */
    public function run(array $data): Company
    {
        return DB::transaction(function () use ($data): Company {
            /** @var array<string, mixed> $attributes */
            $attributes = Arr::except($data, ['domains', 'logo']);
            $company = $this->createCompanyTask->run($attributes);

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
