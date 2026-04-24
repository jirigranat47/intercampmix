@extends('layout')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-card shadow-xl rounded-2xl overflow-hidden border border-theme">
        <div class="bg-nav pt-6 pb-2 px-8 text-center text-white">
            <h1 class="text-2xl font-bold mb-1">{{ __('Najdi svou skupinu') }}</h1>
            <p class="text-blue-100 opacity-90">{{ __('Zadej svůj kód a zjisti, do které skupiny patříš.') }}</p>
        </div>

        <div class="px-8 pb-8 pt-2">
            <form action="{{ route('participant.search') }}" method="GET" class="space-y-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Kód účastníka (např. S1-17-7)') }}</label>
                    <input type="text" name="code" id="code" required 
                        placeholder="{{ __('Zadejte kód') }}" 
                        value="{{ old('code', $code ?? '') }}"
                        class="block w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg uppercase font-mono tracking-widest placeholder-gray-400">
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all active:scale-95">
                    🔍 {{ __('Vyhledat skupinu') }}
                </button>
            </form>

            @if(session('error'))
                <div class="mt-6 p-4 bg-red-50 rounded-xl border border-red-200 text-red-700 text-center animate-pulse">
                    {{ session('error') }}
                </div>
            @endif

            @if(isset($participant))
                <div class="mt-10 p-1 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all">
                    <div class="bg-card rounded-[1.4rem] p-8 text-center">
                        <div class="inline-block px-4 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold tracking-widest uppercase mb-6">
                            {{ __('Subcamp') }} {{ $participant->originalGroup->subcamp ?? '?' }}
                        </div>

                        <div class="mb-6">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1 block">{{ __('Troop Name / Název týmu') }}</span>
                            <h2 class="text-4xl font-black text-gray-900 leading-tight">
                                {{ $participant->originalGroup->troop_name ?? __('Neznámý tým') }}
                            </h2>
                        </div>
                        <div class="mb-8">
                            <span class="block text-xs text-gray-400 uppercase tracking-widest mb-1">
                                @if($participant->is_leader)
                                    <span class="text-blue-600 font-black">{{ __('LEADER / VEDOUCÍ') }}</span>
                                @else
                                    {{ __('Účastník') }}
                                @endif
                            </span>                            
                        </div>
                        
                        <div class="mb-8">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1 block">{{ __('Nationality / Národnost') }}</span>
                            <div class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 uppercase tracking-wider">
                                {{ $participant->country }}
                            </div>
                        </div>

                        @if($participant->originalGroup && $participant->originalGroup->leader_name)
                        <div class="mt-8 pt-8 border-t border-theme">
                            <div class="mb-4">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1 block">{{ __('Main Leader') }}</span>
                                <div class="text-lg font-bold text-gray-800">
                                    {{ $participant->originalGroup->leader_name }}
                                </div>
                            </div>
                            @if($participant->originalGroup->leader_phone)
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1 block">{{ __('Phone') }}</span>
                                <a href="tel:{{ $participant->originalGroup->leader_phone }}" class="text-xl font-black text-blue-600 hover:text-blue-800 transition-colors">
                                    {{ $participant->originalGroup->leader_phone }}
                                </a>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                    </div>
                </div>
            @endif
    </div>
</div>
@endsection
