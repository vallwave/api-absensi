<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenModel extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'absen_id';

    protected $fillable = [
        'absen_id',
        'user_id',
        'tanggal',
        'clockin',
        'clockout',
        'foto',
        'confidence',
        'emotion',
        'foto_out',
        'confidence_out',
        'emotion_out',
        'tipe',
        'alasan',
        'latitude',
        'longitude',
        'latitude_out',
        'longitude_out',
        'created_at',
        'updated_at',
    ];
}
