<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelAbsen extends Model
{
    use HasFactory;

    protected $table = 'tbabsensi';
    protected $fillable = [
        'npm_mahasiswa', 'kode_matkul', 'hari_absen', 'tanggal_absen', 'jam_absen_masuk',
        'jam_absen_selesai', 'acc_dosen','istolak', 'foto_mhs','foto_mhs_selesai'
    ];
}
