<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TaskModel extends Model
{
    use HasFactory,Notifiable,HasApiTokens;

    protected $table = 'tasks';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'id_projecet',
        'id_label',
        'id_list',
        'id_keyresult',
        'task_name',
        'description',
        'start_date',
        'due_date',
        'cover',
        'target',
        'persentase',
        'join',
        'task_order',
        'status',
        'created_at',
        'updated_at',
    ];
    
}
