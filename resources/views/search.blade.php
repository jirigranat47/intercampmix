@extends('layout')

@yield('title', 'Najdi svou skupinu')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <div class="bg-blue-800 px-6 py-8 text-center text-white">
            <h1 class="text-2xl font-bold mb-2">Najdi svou skupinu</h1>
            <p class="text-blue-100 opacity-90">Zadej svůj kód a zjisti, do které skupiny patříš.</p>
        </div>

        <div class="p-8">
            <form action="{{ route('participant.search.submit') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kód účastníka (např. SC1_G01_1)</label>
                    <input type="text" name="code" id="code" required 
                        placeholder="Zadejte kód" 
                        value="{{ old('code', $code ?? '') }}"
                        class="block w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg uppercase font-mono tracking-widest placeholder-gray-400">
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all active:scale-95">
                    🔍 Vyhledat skupinu
                </button>
            </form>

            @if(session('error'))
                <div class="mt-6 p-4 bg-red-50 rounded-xl border border-red-200 text-red-700 text-center animate-pulse">
                    {{ session('error') }}
                </div>
            @endif

            @if(isset($participant))
                <div class="mt-10 p-1 bg-gradient-to-br from-green-400 to-blue-500 rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all">
                    <div class="bg-white rounded-[1.4rem] p-8 text-center">
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Tvá cílová skupina je:</div>
                        <div class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-blue-700 mb-4 font-mono">
                            {{ $participant->target_group }}
                        </div>
                        
                        <div class="pt-6 border-t border-gray-100 mt-6 grid grid-cols-2 gap-4">
                            <div class="text-left">
                                <span class="block text-xs text-gray-400 uppercase">Jméno</span>
                                <span class="font-bold">{{ $participant->first_name }} {{ $participant->last_name }}</span>
                            </div>
                            <div class="text-left">
                                <span class="block text-xs text-gray-400 uppercase">Země</span>
                                <span class="font-bold">{{ $participant->country }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 text-sm text-gray-500">
                             Nezapomeň si tento název zapamatovat!
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-12 text-center text-gray-400 text-sm">
        &copy; 2026 Intercamp Mixer - Strategické Rozřazování
    </div>
</div>
@endsection
