<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentProjects extends Model
{
    use HasFactory;

    public function tasks(){
        return $this->hasMany(Task::class, 'projectid');
    }

    public function client(){
        return $this->belongsTo(Clients::class, 'client', 'id');
    }



}
