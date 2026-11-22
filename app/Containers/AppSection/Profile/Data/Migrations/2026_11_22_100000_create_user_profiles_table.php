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
        Schema::create('user_profiles', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->jsonb('first_name');
            $table->jsonb('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->jsonb('location')->nullable();
            $table->jsonb('headline')->nullable();
            $table->jsonb('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
