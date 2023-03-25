<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBranch extends Model
{
    use HasFactory;


    public function branch(){
        return $this->belongsTo(Branches::class, 'id', 'branch');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user', 'id');
    }

}
