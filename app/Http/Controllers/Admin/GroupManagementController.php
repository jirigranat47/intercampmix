<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OriginalGroup;
use Illuminate\Http\Request;

class GroupManagementController extends Controller
{
    public function search(Request $request)
    {
        return view('admin.groups.search');
    }

    public function autocomplete(Request $request)
    {
        $query = strtolower($request->get('query'));
        if (!$query) return response()->json([]);

        $groups = OriginalGroup::whereRaw('LOWER(troop_name) LIKE ?', ["%{$query}%"])
            ->orWhereRaw('LOWER(country) LIKE ?', ["%{$query}%"])
            ->limit(10)
            ->get(['id', 'troop_name', 'country', 'subcamp']);

        return response()->json($groups);
    }

    public function edit($id)
    {
        $group = OriginalGroup::findOrFail($id);
        return view('admin.groups.edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = OriginalGroup::findOrFail($id);

        $request->validate([
            'leader_name' => 'required|string|max:255',
            'leader_phone' => ['required', 'string', 'regex:/^\+[0-9]{7,15}$/'],
        ], [
            'leader_phone.regex' => __('Telefon musí být v mezinárodním formátu začínajícím + (např. +420123456789).'),
        ]);

        $group->update([
            'leader_name' => $request->leader_name,
            'leader_phone' => $request->leader_phone,
        ]);

        return redirect()->route('admin.groups.search')->with('success', __('Kontakt byl úspěšně aktualizován.'));
    }
}
