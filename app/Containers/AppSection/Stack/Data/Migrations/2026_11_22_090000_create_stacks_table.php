<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create('stacks', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX stacks_name_lower_unique ON stacks (lower(name))');
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('stacks');
    }
};
