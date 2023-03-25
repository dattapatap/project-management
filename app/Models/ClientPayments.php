<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPayments extends Model
{
    use HasFactory;

    public function addedBy(){
        return $this->hasOne(User::class, 'id', 'created_by')->select('id', 'name');
    }

    public function packages(){
        return $this->belongsTo(ClientPackages::class, 'package_id', 'id');
    }



}
