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
        Schema::create('vacancies', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained()->restrictOnDelete();
            $table->foreignId('vacancy_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('url', 2048)->nullable();
            $table->longText('description')->nullable();
            $table->string('location')->nullable();
            $table->string('work_format', 20)->nullable();
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->string('currency', 3)->nullable();
            $table->date('applied_at')->nullable();
            $table->dateTime('next_action_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
