@extends('layout')

@section('title', __('Správa přístupových tokenů'))

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">{{ __('Správa přístupových tokenů') }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('Zde můžete generovat a mazat přístupové tokeny pro různé uživatele.') }}
        </p>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8 p-6">
    <h3 class="text-lg font-medium mb-4">{{ __('Generovat nový token') }}</h3>
    <form action="{{ route('admin.tokens.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        @csrf
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Popis (např. Pro vedoucího Subcamp 1)') }}</label>
            <input type="text" name="description" id="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
            <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="viewer">{{ __('Prohlížení (Viewer)') }}</option>
                <option value="admin">{{ __('Administrátor (Admin)') }}</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Vygenerovat Token') }}
            </button>
        </div>
    </form>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Popis') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Role') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Odkaz pro přihlášení') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Naposledy použito') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Akce') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($tokens as $t)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $t->description }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $t->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ strtoupper($t->role) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                   <div class="flex items-center space-x-2">
                        <input type="text" readonly value="{{ route('auth.login', $t->token) }}" class="text-xs bg-gray-50 border-none w-64 p-1 rounded">
                        <button onclick="copyToClipboard('{{ route('auth.login', $t->token) }}')" class="text-indigo-600 hover:text-indigo-900">📋</button>
                   </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : __('Nikdy') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <form action="{{ route('admin.tokens.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Opravdu smazat?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Smazat') }}</button>
                    </form>
                </td>
            </tr>
            @endforeach
            @if($tokens->isEmpty())
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">{{ __('Žádné vygenerované tokeny.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert("{{ __('Odkaz zkopírován!') }}");
    });
}
</script>
@endsection
