<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\DeleteCompanyAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteCompanyAction::class)]
final class DeleteCompanyActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesCompanyWithLogoDirectoryAndKeepsDomains(): void
    {
        Storage::fake('public');
        $domain = Domain::factory()->createOne();
        $company = Company::factory()->createOne();
        $company->domains()->attach($domain->id);
        $other = Company::factory()->createOne();
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));
        app(ReplaceCompanyLogoTask::class)->run($other, UploadedFile::fake()->image('logo.png'));

        $result = app(DeleteCompanyAction::class)->run($company->id);

        $this->assertTrue($result);
        $this->assertModelMissing($company);
        $this->assertModelExists($domain);
        $this->assertDatabaseMissing('media', ['model_id' => $company->id, 'model_type' => $company->getMorphClass()]);
        Storage::disk('public')->assertMissing("companies/{$company->id}");
        Storage::disk('public')->assertExists("companies/{$other->id}/logo.png");
    }
}
