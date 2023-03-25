<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function emp(){
        return $this->hasOne(Employees::class, 'user', 'id');
    }

    public function departments(){
        return $this->hasOne(UserDepartment::class, 'user', 'id');
    }

    public function branch(){
        return $this->belongsTo(UserBranch::class, 'user', 'id');
    }



    public function receivesBroadcastNotificationsOn()
    {
        return 'post_like.'.$this->id;
    }



}
