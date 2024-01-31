<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelMatkul extends Model
{
    use HasFactory;

    protected $table = 'tbmatkul';
    protected $fillable = ['kode_matkul', 'mata_kuliah_dosen', 'kode_dosen', 'nama_dosen', 'sks'];
}
