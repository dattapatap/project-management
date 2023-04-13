<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }
    public function createdby(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function project(){
        return $this->belongsTo(DepartmentProjects::class, 'projectid', 'id');
    }

    public function logs(){
        return $this->hasMany(TaskLog::class, 'taskid', 'id');
    }

    public function comments(){
        return $this->hasMany(TaskComment::class, 'taskid', 'id');
    }



}
