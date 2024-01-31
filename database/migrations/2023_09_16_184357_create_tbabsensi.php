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
        Schema::create('tbabsensi', function (Blueprint $table) {
            $table->id();
            $table->string('npm_mahasiswa');
            $table->string('kode_matkul');
            $table->string('hari_absen');
            $table->date('tanggal_absen');
            $table->time('jam_absen_masuk');
            $table->time('jam_absen_selesai');
            $table->boolean('accdosen')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbabsensi');
    }
};
