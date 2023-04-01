<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentProjectHistory extends Model
{
    use HasFactory;

    public function histories()
    {
        return $this->morphTo();
    }

}
