<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictToCsd
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $departmentId = $user->departments->department ?? null;

        if ($user->hasRole(['Admin', 'CSD-Executive', 'Branch-Manager']) ||
            ($user->hasRole('Team-Leader') && $departmentId == 3)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. This section is reserved exclusively for the Customer Service Department.');
    }
}
