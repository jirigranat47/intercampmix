<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AccessToken;
use Illuminate\Support\Facades\Cookie;

class TokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Get token from Header, Session, or Cookie (synced from localStorage)
        $token = $request->header('X-Access-Token') 
                 ?? $request->cookie('access_token') 
                 ?? session('access_token');

        $role = 'none';
        $rootToken = config('app.admin_root_token');

        if ($token) {
            if ($rootToken && $token === $rootToken) {
                $role = 'admin';
            } else {
                $dbToken = AccessToken::where('token', $token)->first();
                if ($dbToken) {
                    $role = $dbToken->role;
                    // Update last used
                    $dbToken->update(['last_used_at' => now()]);
                }
            }
        }

        // 2. Share role with views
        view()->share('userRole', $role);
        $request->merge(['userRole' => $role]);

        // 3. Exempt public auth routes
        if ($request->is('auth/*') || $request->is('logout')) {
            return $next($request);
        }

        // 4. If no token, redirect to a "restricted" view or show error
        // But for the search page (root), we want to show a clean "Enter your link" message
        if ($role === 'none') {
            if ($request->is('/')) {
                // Return a special minimal view for unauthorized users
                return response()->view('auth.no_access');
            }
            return redirect('/')->withErrors(__('Tato sekce vyžaduje přihlášení.'));
        }

        // 5. Enforce access control for /admin routes
        if ($request->is('admin') || $request->is('admin/*')) {
            // Viewers can only see DB and Groups/Stats, but not Import or Token management
            if ($role === 'viewer') {
                $allowedViewerRoutes = [
                    'admin.db',
                    'admin.groups',
                    'admin.stats'
                ];
                
                if (!in_array($request->route()->getName(), $allowedViewerRoutes)) {
                    return redirect('/')->withErrors(__('Nedostatečná oprávnění pro tuto akci.'));
                }
            }
        }

        return $next($request);
    }
}
