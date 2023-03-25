<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function branch(){
        return $this->belongsTo(Branches::class, 'branchid','id')->select(['id','name','code'])->orderBy('id', 'asc');
    }

    public function users(){
        return $this->hasMany(UserDepartment::class, 'department', 'id')->select('department', 'id', 'user');
    }

}
