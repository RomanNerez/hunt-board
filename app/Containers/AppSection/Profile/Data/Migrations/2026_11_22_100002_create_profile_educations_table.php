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
        Schema::create('profile_educations', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->string('institution')->nullable();
            $table->date('started_on')->nullable();
            $table->date('finished_on')->nullable();
            $table->string('url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort');
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_educations');
    }
};
