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
        Schema::create('contact_vacancy_source', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_source_id')->constrained()->cascadeOnDelete();
            $table->string('url', 2048)->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'vacancy_source_id']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_vacancy_source');
    }
};
