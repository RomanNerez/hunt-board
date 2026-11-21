<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Media;

use App\Containers\AppSection\Company\Media\CompanyLogoPathGenerator;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

#[CoversClass(CompanyLogoPathGenerator::class)]
final class CompanyLogoPathGeneratorTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testBuildsPathsFromCompanyId(): void
    {
        $media = new Media();
        $media->model_type = Company::class;
        $media->model_id = 7;

        $generator = new CompanyLogoPathGenerator();

        $this->assertSame('companies/7/', $generator->getPath($media));
        $this->assertSame('companies/7/conversions/', $generator->getPathForConversions($media));
        $this->assertSame('companies/7/responsive-images/', $generator->getPathForResponsiveImages($media));
    }

    /**
     * @return void
     */
    public function testIsRegisteredForCompanyMedia(): void
    {
        $media = new Media();
        $media->model_type = Company::class;
        $media->model_id = 7;

        $this->assertInstanceOf(CompanyLogoPathGenerator::class, PathGeneratorFactory::create($media));
    }
}
