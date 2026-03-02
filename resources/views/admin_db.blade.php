@extends('layout')

@section('title', 'Prohlížeč Databáze')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Obsah Databáze</h2>
    <p class="mt-1 text-sm text-gray-500">
        Zde si můžete prohlédnout tabulky s nahranými daty a jak algoritmus přiřadil cílové skupiny.
    </p>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8 p-4">
    <form action="{{ route('admin.db') }}" method="GET" class="flex items-center space-x-4">
        <div>
            <label for="subcamp" class="block text-sm font-medium text-gray-700">Filtr Subcamp</label>
            <select id="subcamp" name="subcamp" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Všechny Subcampy</option>
                @foreach($allSubcamps as $sc)
                    <option value="{{ $sc }}" {{ $selectedSubcamp == $sc ? 'selected' : '' }}>Subcamp {{ $sc }}</option>
                @endforeach
            </select>
        </div>
        <div class="pt-5">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Filtrovat
            </button>
            @if($selectedSubcamp)
                <a href="{{ route('admin.db') }}" class="ml-2 text-sm text-gray-500 hover:text-gray-700 underline">Zrušit filtr</a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Tabulka: Original Groups (Skupiny z Excelu)
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Celkem záznamů v pohledu: {{ count($groups) }}
            </p>
        </div>
    </div>
    <div class="border-t border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcamp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Troop Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Děti</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Vedoucí</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($groups as $g)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $g->subcamp }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $g->order_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $g->country }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::limit($g->troop_name, 30) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $g->number_of_children }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center font-bold">{{ $g->number_of_leaders }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Tabulka: Vygenerované Cílové Skupiny (Target Groups)
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Zobrazeno {{ $targetGroups->count() }} celkových skupin.
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
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-100 flex justify-between items-center">
                        <h4 class="text-md font-bold text-indigo-700">{{ $groupName }}</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $leaderCount }} + {{ $kidCount }} Členů
                        </span>
                    </div>
                    <div class="px-4 py-3">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($members as $p)
                                <li class="py-2 flex">
                                    <div class="ml-3 flex-1 flex flex-col">
                                        <div class="flex justify-between items-center">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $p->first_name }} {{ $p->last_name }} 
                                            </p>
                                            @if($p->is_leader)
                                                <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-sm bg-blue-100 text-blue-800">Vedoucí</span>
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
                                <p class="text-xs text-gray-500 text-center py-2">Žádní účastníci</p>
                            @endif
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        @if($targetGroups->isEmpty())
            <div class="text-center py-8 text-gray-500">
                Nebyly nalezeny žádné cílové skupiny. Zkuste spustit rozřazovací algoritmus.
            </div>
        @endif
    </div>
</div>
@endsection
