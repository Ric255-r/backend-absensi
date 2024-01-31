<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbabsensi', function (Blueprint $table) {
            $table->text('foto_mhs')->nullable()->after('accdosen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbabsensi', function (Blueprint $table) {
            $table->dropColumn('foto_mhs');
        });
    }
};
