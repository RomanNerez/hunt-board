<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\DeleteCompanyLogoTask;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteCompanyLogoTask::class)]
final class DeleteCompanyLogoTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testRemovesLogoAndCompanyDirectory(): void
    {
        Storage::fake('public');
        $company = Company::factory()->createOne();
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));

        app(DeleteCompanyLogoTask::class)->run($company);

        $this->assertSame(0, $company->media()->count());
        Storage::disk('public')->assertMissing("companies/{$company->id}");
    }
}
