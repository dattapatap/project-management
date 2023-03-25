<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;

    public function department(){
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function teammembers(){
        return $this->hasMany(TeamMembers::class, 'team', 'id');
    }
}
