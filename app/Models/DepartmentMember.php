<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentMember extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class, 'user', 'id');
    }


    public function histories()
    {
        return $this->morphMany(DepartmentProjectHistory::class, 'histories');
    }

}
