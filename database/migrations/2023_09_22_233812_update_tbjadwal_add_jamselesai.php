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
        Schema::table('tbjadwal', function (Blueprint $table) {
            $table->time('jam_selesai')->after('jam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbjadwal', function (Blueprint $table) {
            $table->dropColumn('jam_selesai');
        });
    }
};
