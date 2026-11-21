<?php

namespace App\Containers\AppSection\Company\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ReplaceCompanyLogoTask extends ParentTask
{
    /**
     * @param Company $company
     * @param UploadedFile $file
     *
     * @return Media
     *
     * @throws FileCannotBeAdded
     */
    public function run(Company $company, UploadedFile $file): Media
    {
        $company->clearMediaCollection(Company::LOGO_COLLECTION);

        return $company->addMedia($file)
            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName('logo.' . $this->extensionFromMimeType($file))
            ->toMediaCollection(Company::LOGO_COLLECTION);
    }

    /**
     * @param UploadedFile $file
     *
     * @return string
     */
    private function extensionFromMimeType(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? strtolower($file->getClientOriginalExtension());

        return 'jpeg' === $extension ? 'jpg' : $extension;
    }
}
