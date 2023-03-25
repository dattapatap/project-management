<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPackages extends Model
{
    use HasFactory;

    public function clients(){
        return $this->belongsTo(Clients::class, 'client', 'id')->select('id', 'name');
    }

    public function projects(){
        return $this->belongsTo(DepartmentProjects::class, 'project_id', 'id')->select('id', 'project_name');
    }

    public function addedby(){
        return $this->belongsTo(User::class, 'created_by', 'id')->select('id', 'name');
    }


}
