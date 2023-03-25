<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMembers extends Model
{
    use HasFactory;

    public function users(){
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function team(){
        return $this->belongsTo(Teams::class, 'team', 'id');
    }



}
