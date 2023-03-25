<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDepartment extends Model
{
    use HasFactory;

    public function dept(){
        return $this->belongsTo(Department::class, 'department', 'id');
    }


    public function userdetail(){
        return $this->belongsTo(User::class, 'user', 'id');
    }

}
