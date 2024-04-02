<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shift';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'id_role',
        'name',
        'jam_mulai',
        'jam_selesai',
        'created_at',
        'updated_at'

    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }
}
