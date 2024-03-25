<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_company',
        'code',
        'name',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id'; // Set primary key to 'id'
    public $incrementing = false; // Tell Laravel that primary key is not incrementing
}
