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
            $table->text('foto_mhs_selesai')->nullable()->after('foto_mhs');
            $table->boolean('istolak')->default(0)->after('accdosen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbabsensi', function (Blueprint $table) {
            $table->dropColumn(['foto_mhs_selesai', 'istolak']);
        });
    }
};
