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
        Schema::table('vacancies', static function (Blueprint $table): void {
            $table->dropColumn('notes');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('vacancies', static function (Blueprint $table): void {
            $table->text('notes')->nullable();
        });
    }
};
