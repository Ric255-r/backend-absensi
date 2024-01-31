<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelJoinMatkul extends Model
{
    use HasFactory;

    protected $table = 'tbjoin_matkul';
    protected $fillable = ['kode_matkul', 'npm'];

}
