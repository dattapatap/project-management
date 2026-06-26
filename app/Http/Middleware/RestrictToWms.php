<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictToWms
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

        // Allow Admin, Branch-Manager, Project-Manager, WMS roles, and WMS Team Leaders (department == 2)
        if ($user->hasRole(['Admin', 'Branch-Manager', 'Project-Manager', 'Developer', 'Designer', 'Seo-Developer', 'Accountant']) ||
            ($user->hasRole('Team-Leader') && $departmentId == 2)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. This section is reserved exclusively for the Operations/WMS department.');
    }
}
