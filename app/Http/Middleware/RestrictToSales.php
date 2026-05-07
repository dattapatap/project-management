<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictToSales
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $departmentId = $user->departments->department ?? null;

        // Allow Admin, Sales-Executive, and Sales Team-Leaders (department == 1)
        if ($user->hasRole('Admin') || 
            $user->hasRole('Sales-Executive') || 
            ($user->hasRole('Team-Leader') && $departmentId == 1)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. This section is reserved exclusively for the Sales department.');
    }
}
