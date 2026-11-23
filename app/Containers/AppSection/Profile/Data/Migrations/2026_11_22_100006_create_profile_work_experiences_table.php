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
        Schema::create('profile_work_experiences', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->jsonb('company_name');
            $table->jsonb('role');
            $table->date('started_on');
            $table->date('finished_on')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_on']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_work_experiences');
    }
};
