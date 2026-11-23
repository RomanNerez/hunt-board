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
        Schema::create('profile_projects', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_experience_id')->constrained('profile_work_experiences')->cascadeOnDelete();
            $table->jsonb('name');
            $table->date('started_on');
            $table->date('finished_on')->nullable();
            $table->jsonb('description')->nullable();
            $table->timestamps();

            $table->index(['work_experience_id', 'started_on']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_projects');
    }
};
