<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentProjectHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function histories()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'addedby', 'id');
    }

}
