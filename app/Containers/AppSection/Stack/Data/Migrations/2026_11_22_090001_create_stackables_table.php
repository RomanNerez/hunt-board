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
        Schema::create('stackables', static function (Blueprint $table): void {
            $table->foreignId('stack_id')->constrained()->cascadeOnDelete();
            $table->morphs('stackable');
            $table->unsignedSmallInteger('sort');

            $table->primary(['stack_id', 'stackable_type', 'stackable_id']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('stackables');
    }
};
