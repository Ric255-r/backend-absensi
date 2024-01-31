<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelJadwal extends Model
{
    use HasFactory;
    protected $table = 'tbjadwal';
    protected $fillable = ['hari', 'jam', 'jam_selesai', 'kode_matkul'];
}
