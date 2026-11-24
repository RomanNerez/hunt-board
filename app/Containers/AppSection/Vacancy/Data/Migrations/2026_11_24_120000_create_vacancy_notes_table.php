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
        Schema::create('vacancy_notes', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['vacancy_id', 'occurred_at']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancy_notes');
    }
};
