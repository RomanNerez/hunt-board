<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create('vacancy_vacancy_source', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_source_id')->constrained()->cascadeOnDelete();
            $table->string('url', 2048)->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'vacancy_source_id']);
        });

        Schema::table('vacancies', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('vacancy_source_id');
            $table->dropColumn('url');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('vacancies', static function (Blueprint $table): void {
            $table->foreignId('vacancy_source_id')->nullable()->after('status_id')->constrained()->nullOnDelete();
            $table->string('url', 2048)->nullable()->after('title');
        });

        Schema::dropIfExists('vacancy_vacancy_source');
    }
};
