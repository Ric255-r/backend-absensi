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
        Schema::table('tbmatkul', function (Blueprint $table) {
            $table->string('nama_dosen')->after('kode_dosen');
            $table->integer('sks')->after('nama_dosen')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbmatkul', function (Blueprint $table) {
            $table->dropColumn(['nama_dosen', 'sks']);
        });
    }
};
