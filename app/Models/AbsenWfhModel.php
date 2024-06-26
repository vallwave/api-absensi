<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AbsenWfhModel extends Model
{

    use HasApiTokens,HasFactory,Notifiable;

    protected $table = 'absensi_wfh';
    protected $primaryKey = 'absen_wfh_id';

    protected $fillable = [
        'absen_wfh_id',
        'absen_id',
        'clockin',
        'clockout',
        'foto',
        'confidence',
        'emotion',
        'foto_out',
        'confidence_out',
        'emotion_out',
        'latitude',
        'longitude',
        'latitude_out',
        'longitude_out',
        'created_at',
        'updated_at'
    ];
}
