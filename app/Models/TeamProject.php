<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamProject extends Model
{
    use HasFactory;
    
    protected $fillable = ['projectid', 'teamid', 'assigned_by', 'assigned_date'];


    public function team()
    {
        return $this->belongsTo(Teams::class, 'teamid', 'id');
    }
}
