<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessToken;

class AuthController extends Controller
{
    /**
     * Set token in localStorage and session
     */
    public function login(Request $request, $token)
    {
        $rootToken = config('app.admin_root_token');
        $valid = ($rootToken && $token === $rootToken);
        
        if (!$valid) {
            $valid = AccessToken::where('token', $token)->exists();
        }

        if (!$valid) {
            return redirect('/')->withErrors(__('Neplatný přístupový token.'));
        }

        return view('auth.login_sync', ['token' => $token]);
    }

    /**
     * Logout logic
     */
    public function logout()
    {
        return view('auth.logout_sync');
    }
}
