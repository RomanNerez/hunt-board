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
        Schema::table('profile_languages', static function (Blueprint $table): void {
            $table->string('description', 500)->nullable()->after('level');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('profile_languages', static function (Blueprint $table): void {
            $table->dropColumn('description');
        });
    }
};
