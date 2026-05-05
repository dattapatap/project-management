<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $fillable = ['user_id', 'activity', 'ip_address', 'location', 'user_agent', 'details'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($activity, $details = null)
    {
        $ip = \Request::ip();
        $location = null;

        // Only fetch location for Login activity to save performance
        if ($activity === 'Login' && $ip !== '127.0.0.1') {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['status'] === 'success') {
                        $location = ($data['city'] ?? 'Unknown') . ', ' . ($data['country'] ?? 'Unknown');
                    }
                }
            } catch (\Exception $e) {
                // Silently fail if API is down
            }
        }

        return self::create([
            'user_id'    => \Auth::id(),
            'activity'   => $activity,
            'ip_address' => $ip,
            'location'   => $location,
            'user_agent' => \Request::header('User-Agent'),
            'details'    => $details
        ]);
    }
}
