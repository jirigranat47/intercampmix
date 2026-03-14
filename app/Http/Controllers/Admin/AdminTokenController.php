<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccessToken;
use Illuminate\Support\Str;

class AdminTokenController extends Controller
{
    public function index(Request $request)
    {
        // Only ROOT admin (config token) can manage tokens
        if ($request->userRole !== 'admin') {
             return redirect('/')->withErrors(__('Pouze hlavní administrátor může spravovat tokeny.'));
        }

        $tokens = AccessToken::orderBy('created_at', 'desc')->get();
        return view('admin.tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        if ($request->userRole !== 'admin') {
             return back()->withErrors(__('Nedostatečná oprávnění.'));
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'role' => 'required|in:admin,viewer',
        ]);

        AccessToken::create([
            'token' => Str::random(32),
            'role' => $request->role,
            'description' => $request->description,
        ]);

        return back()->with('success', __('Token byl úspěšně vygenerován.'));
    }

    public function destroy(Request $request, $id)
    {
        if ($request->userRole !== 'admin') {
             return back()->withErrors(__('Nedostatečná oprávnění.'));
        }

        AccessToken::findOrFail($id)->delete();

        return back()->with('success', __('Token byl smazán.'));
    }
}
