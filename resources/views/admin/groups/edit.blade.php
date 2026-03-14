@extends('layout')

@section('title', __('Upravit kontakt skupiny'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-gray-900">{{ __('Upravit kontakt') }}</h2>
            <p class="text-gray-500">{{ $group->troop_name }} ({{ $group->country }})</p>
        </div>
        <a href="{{ route('admin.groups.search') }}" class="text-sm font-bold text-blue-600 hover:underline">← {{ __('Zpět na hledání') }}</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Info Column -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-blue-50 rounded-3xl p-6 border border-blue-100">
                <h3 class="text-blue-900 font-bold mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ __('Detaily skupiny') }}
                </h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-blue-700/60 font-medium uppercase tracking-wider text-[10px]">{{ __('Subcamp') }}</dt>
                        <dd class="text-blue-900 font-bold text-lg">{{ $group->subcamp }}</dd>
                    </div>
                    <div class="flex space-x-8">
                        <div>
                            <dt class="text-blue-700/60 font-medium uppercase tracking-wider text-[10px]">{{ __('Děti') }}</dt>
                            <dd class="text-blue-900 font-bold text-lg">{{ $group->number_of_children }}</dd>
                        </div>
                        <div>
                            <dt class="text-blue-700/60 font-medium uppercase tracking-wider text-[10px]">{{ __('Vedoucí') }}</dt>
                            <dd class="text-blue-900 font-bold text-lg">{{ $group->number_of_leaders }}</dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Form Column -->
        <div class="md:col-span-2">
            <div class="bg-card shadow-xl rounded-3xl p-8 border border-theme">
                <form action="{{ route('admin.groups.update', $group->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="leader_name" class="block text-sm font-bold text-gray-700 mb-2">{{ __('Jméno vedoucího') }}</label>
                        <input type="text" name="leader_name" id="leader_name" 
                            value="{{ old('leader_name', $group->leader_name) }}"
                            required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium">
                        @error('leader_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="leader_phone" class="block text-sm font-bold text-gray-700 mb-2">{{ __('Telefon vedoucího') }}</label>
                        <input type="text" name="leader_phone" id="leader_phone" 
                            value="{{ old('leader_phone', $group->leader_phone) }}"
                            placeholder="{{ __('+420...') }}"
                            required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium font-mono">
                        <p class="mt-2 text-xs text-gray-400 italic">
                            {{ __('Musí začínat na + (např. +420 777 666 555)') }}
                        </p>
                        @error('leader_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg shadow-blue-500/20 active:scale-95">
                            {{ __('Uložit změny') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
