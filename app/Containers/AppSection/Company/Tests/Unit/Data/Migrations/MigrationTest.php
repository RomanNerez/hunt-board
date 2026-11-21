<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return list<array{string}>
     */
    public static function dictionaryTableProvider(): array
    {
        return [
            ['company_types'],
            ['domains'],
        ];
    }

    /**
     * @param string $table
     *
     * @return void
     */
    #[DataProvider('dictionaryTableProvider')]
    public function testDictionaryTableHasExpectedColumns(string $table): void
    {
        $this->assertDatabaseTable($table, [
            'id' => 'int8',
            'name' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @param string $table
     *
     * @return void
     */
    #[DataProvider('dictionaryTableProvider')]
    public function testDictionaryNameIsUnique(string $table): void
    {
        $this->assertTrue($this->hasUniqueIndexOn($table, ['name']));
    }

    /**
     * @return void
     */
    public function testCompaniesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('companies', [
            'id' => 'int8',
            'name' => 'varchar',
            'description' => 'text',
            'company_type_id' => 'int8',
            'website_url' => 'varchar',
            'vacancies_url' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
        $this->assertTrue($this->hasUniqueIndexOn('companies', ['name']));
    }

    /**
     * @return void
     */
    public function testCompanyDomainTableHasCompositePrimaryKey(): void
    {
        $this->assertDatabaseTable('company_domain', [
            'company_id' => 'int8',
            'domain_id' => 'int8',
        ]);

        $primary = collect(Schema::getIndexes('company_domain'))->firstWhere('primary', true);

        $this->assertNotNull($primary);
        $this->assertSame(['company_id', 'domain_id'], $primary['columns']);
    }

    /**
     * @param string $table
     * @param list<string> $columns
     *
     * @return bool
     */
    private function hasUniqueIndexOn(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if ($index['unique'] && $index['columns'] === $columns) {
                return true;
            }
        }

        return false;
    }
}
