@extends('layout')

@section('title', __('Prohlížeč Databáze'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-gray-900 leading-tight">{{ __('Obsah databáze') }}</h2>
        <p class="text-gray-500">{{ __('Zde si můžete prohlédnout nahraná data a přiřazené skupiny.') }}</p>
    </div>
</div>

<div class="bg-card shadow-xl rounded-3xl mb-8 p-6 border border-theme">
    <form action="{{ route('admin.db') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
        <div class="w-full sm:w-auto min-w-[200px]">
            <label for="subcamp" class="block text-sm font-bold text-gray-700 mb-1">{{ __('Filtr Subcamp') }}</label>
            <select id="subcamp" name="subcamp" class="w-full px-4 py-2 rounded-xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all bg-white">
                <option value="">{{ __('Všechny Subcampy') }}</option>
                @foreach($allSubcamps as $sc)
                    <option value="{{ $sc }}" {{ $selectedSubcamp == $sc ? 'selected' : '' }}>{{ __('Subcamp') }} {{ $sc }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center space-x-2 w-full sm:w-auto">
            <button type="submit" class="flex-grow sm:flex-none py-2 px-6 rounded-xl border border-transparent shadow-lg text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 transition-all active:scale-95">
                🔍 {{ __('Filtrovat') }}
            </button>
            @if($selectedSubcamp)
                <a href="{{ route('admin.db') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 underline">{{ __('Zrušit') }}</a>
            @endif
        </div>
    </form>
</div>

<div class="bg-card shadow-xl rounded-3xl border border-theme overflow-hidden mb-8">
    <div class="px-6 py-5 flex justify-between items-center bg-gray-50/50 border-b border-gray-100">
        <div>
            <h3 class="text-lg font-black text-gray-900">
                {{ __('Skupiny z Excelu (Original Groups)') }}
            </h3>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">
                {{ __('Celkem záznamů:') }} {{ count($groups) }}
            </p>
        </div>
    </div>
    
    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">{{ __('Subcamp') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">{{ __('Číslo obj.') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">{{ __('Země') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">{{ __('Název skupiny') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">{{ __('Děti') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">{{ __('Vedoucí') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($groups as $g)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $g->subcamp }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $g->order_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $g->country }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($g->troop_name, 40) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $g->number_of_children }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-black text-main">{{ $g->number_of_leaders }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden divide-y divide-gray-100">
        @foreach($groups as $g)
        <div class="p-4 bg-card hover:bg-gray-50/50 transition-colors">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h4 class="text-sm font-black text-gray-900 leading-tight mb-1">{{ $g->troop_name }}</h4>
                    <p class="text-xs text-gray-500">{{ $g->country }} <span class="mx-1">•</span> {{ $g->order_number }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black uppercase">Subcamp {{ $g->subcamp }}</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-1">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Děti:') }}</span>
                    <span class="text-sm font-bold">{{ $g->number_of_children }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Vedoucí:') }}</span>
                    <span class="text-sm font-black text-main">{{ $g->number_of_leaders }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="bg-card shadow-xl rounded-3xl border border-theme overflow-hidden mb-8">
    <div class="px-6 py-5 bg-gray-50/50 border-b border-gray-100">
        <h3 class="text-lg font-black text-gray-900">
            {{ __('Cílové Skupiny (Target Groups)') }}
        </h3>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">
            {{ __('Zobrazeno') }} {{ $targetGroups->count() }} {{ __('skupin po rozřazení.') }}
        </p>
    </div>

    <div class="border-t border-gray-200 bg-gray-50 p-4">
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($targetGroups as $tg)
                @php 
                    $groupName = $tg->target_group;
                    // Sort members so Leaders ('_X') come first, then regular members by code
                    $members = $participantsByGroup->get($groupName, collect())->sortBy(function($p) {
                        return $p->is_leader ? '0_' . $p->registration_code : '1_' . $p->registration_code;
                    });
                    $leaderCount = $members->where('is_leader', true)->count();
                    $kidCount = $members->where('is_leader', false)->count();
                @endphp
                <div class="bg-card overflow-hidden shadow-lg rounded-2xl border border-theme">
                    <div class="px-4 py-3 border-b border-theme bg-gray-50/50 flex justify-between items-center">
                        <h4 class="text-md font-black text-main">{{ $groupName }}</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                            {{ $leaderCount }} + {{ $kidCount }} {{ __('Členů') }}
                        </span>
                    </div>
                    <div class="px-4 py-3">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($members as $p)
                                <li class="py-2 flex">
                                    <div class="ml-3 flex-1 flex flex-col">
                                        <div class="flex justify-between items-center">
                                            <p class="text-sm font-bold text-main">
                                                {{ $p->first_name }} {{ $p->last_name }} 
                                            </p>
                                            @if($p->is_leader)
                                                <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-sm bg-blue-100 text-blue-800">{{ __('Vedoucí') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex justify-between mt-1 text-xs text-gray-500">
                                            <span>{{ $p->country }}</span>
                                            <span class="text-gray-400">Ord: {{ $p->original_group_id }} | {{ $p->registration_code }}</span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            @if($members->isEmpty())
                                <p class="text-xs text-gray-500 text-center py-2">{{ __('Žádní účastníci') }}</p>
                            @endif
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        @if($targetGroups->isEmpty())
            <div class="text-center py-8 text-gray-500">
                {{ __('Nebyly nalezeny žádné cílové skupiny. Zkuste spustit rozřazovací algoritmus.') }}
            </div>
        @endif
    </div>
</div>
@endsection
