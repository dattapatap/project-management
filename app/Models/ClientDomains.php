<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDomains extends Model
{
    use HasFactory;

    public function clients(){
        return $this->belongsTo(Clients::class, 'client', 'id');
    }

}
