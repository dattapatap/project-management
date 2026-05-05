<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $fillable = ['user_id', 'activity', 'ip_address', 'user_agent', 'details'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($activity, $details = null)
    {
        return self::create([
            'user_id'    => \Auth::id(),
            'activity'   => $activity,
            'ip_address' => \Request::ip(),
            'user_agent' => \Request::header('User-Agent'),
            'details'    => $details
        ]);
    }
}
