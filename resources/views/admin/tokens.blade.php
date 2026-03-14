@extends('layout')

@section('title', __('Správa přístupových tokenů'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-gray-900 leading-tight">{{ __('Správa přístupových tokenů') }}</h2>
        <p class="text-gray-500">{{ __('Zde můžete generovat a mazat přístupové tokeny pro různé uživatele.') }}</p>
    </div>
</div>

<div class="bg-card shadow-xl rounded-3xl mb-8 p-6 border border-theme">
    <h3 class="text-lg font-bold mb-4">{{ __('Generovat nový token') }}</h3>
    <form action="{{ route('admin.tokens.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        @csrf
        <div class="w-full">
            <label for="description" class="block text-sm font-bold text-gray-700 mb-1">{{ __('Popis (např. Pro vedoucího Subcamp 1)') }}</label>
            <input type="text" name="description" id="description" required 
                class="w-full px-4 py-2 rounded-xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
        </div>
        <div class="w-full">
            <label for="role" class="block text-sm font-bold text-gray-700 mb-1">{{ __('Role') }}</label>
            <select name="role" id="role" 
                class="w-full px-4 py-2 rounded-xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all bg-white">
                <option value="viewer">{{ __('Prohlížení (Viewer)') }}</option>
                <option value="subcamp_chief">{{ __('Subcamp Chief (Editace kontaktů)') }}</option>
                <option value="admin">{{ __('Administrátor (Admin)') }}</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full py-2.5 px-6 rounded-xl border border-transparent shadow-lg text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 transition-all active:scale-95">
                ➕ {{ __('Vygenerovat Token') }}
            </button>
        </div>
    </form>
</div>

<div class="bg-card shadow-xl rounded-3xl border border-theme overflow-hidden">
    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Popis') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Role') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Odkaz pro přihlášení') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Naposledy použito') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Akce') }}</th>
                </tr>
            </thead>
            <tbody class="bg-card divide-y divide-gray-200">
                @foreach($tokens as $t)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-900 min-w-[200px]">{{ $t->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($t->role === 'admin')
                            <span class="px-2 py-1 rounded-full text-[10px] font-black bg-red-100 text-red-800">ADMIN</span>
                        @elseif($t->role === 'subcamp_chief')
                            <span class="px-2 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">CHIEF</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-[10px] font-black bg-green-100 text-green-800">VIEWER</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm font-mono text-gray-500">
                       <div class="flex items-center space-x-2">
                            <input type="text" readonly value="{{ route('auth.login', $t->token) }}" 
                                class="text-xs bg-gray-50/50 border border-gray-100 min-w-[200px] flex-grow p-1.5 rounded-lg focus:outline-none">
                            <button onclick="copyToClipboard('{{ route('auth.login', $t->token) }}')" 
                                class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors border border-gray-100 grayscale hover:grayscale-0" 
                                title="{{ __('Kopírovat') }}">📋</button>
                       </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : __('Nikdy') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <form action="{{ route('admin.tokens.destroy', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Opravdu smazat?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">{{ __('Smazat') }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($tokens->isEmpty())
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center text-gray-500 italic">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                            {{ __('Žádné vygenerované tokeny.') }}
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden divide-y divide-gray-100">
        @foreach($tokens as $t)
        <div class="p-6 bg-card">
            <div class="flex justify-between items-start mb-4">
                <div class="pr-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ __('Popis') }}</p>
                    <h4 class="text-sm font-bold text-gray-900 leading-tight">{{ $t->description }}</h4>
                </div>
                <div>
                    @if($t->role === 'admin')
                        <span class="px-2 py-1 rounded-full text-[10px] font-black bg-red-100 text-red-800">ADMIN</span>
                    @elseif($t->role === 'subcamp_chief')
                        <span class="px-2 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">CHIEF</span>
                    @else
                        <span class="px-2 py-1 rounded-full text-[10px] font-black bg-green-100 text-green-800">VIEWER</span>
                    @endif
                </div>
            </div>
            
            <div class="mb-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Přihlašovací odkaz') }}</p>
                <div class="flex items-center space-x-2">
                    <input type="text" readonly value="{{ route('auth.login', $t->token) }}" 
                        class="text-xs bg-gray-50 p-2 rounded-lg w-full border border-gray-100 overflow-hidden text-ellipsis">
                    <button onclick="copyToClipboard('{{ route('auth.login', $t->token) }}')" 
                        class="p-2 bg-blue-50 text-blue-600 rounded-lg border border-blue-100 active:scale-90 transition-transform">📋</button>
                </div>
            </div>

            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>{{ __('Použito:') }} {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : __('Nikdy') }}</span>
                <form action="{{ route('admin.tokens.destroy', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Opravdu smazat?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 font-bold hover:underline">{{ __('Smazat') }}</button>
                </form>
            </div>
        </div>
        @endforeach
        @if($tokens->isEmpty())
        <div class="p-10 text-center text-gray-500 italic">
            {{ __('Žádné vygenerované tokeny.') }}
        </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert("{{ __('Odkaz zkopírován!') }}");
    });
}
</script>
@endsection
