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
        Schema::create('resumes', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('locale', 2)->default('en');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('headline')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
