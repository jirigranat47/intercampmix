@extends('layout')

@section('title', 'Přehled vytvořených skupin')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Přehled vytvořených skupin</h2>
        <p class="mt-1 text-sm text-gray-500">
            Detailní rozpis všech cílových skupin, jejich složení a vedoucích.
        </p>
    </div>
    <div>
        <a href="{{ route('admin.db') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Zpět na databázi
        </a>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8 p-4">
    <form action="{{ route('admin.groups') }}" method="GET" class="flex items-center space-x-4">
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
                <a href="{{ route('admin.groups') }}" class="ml-2 text-sm text-gray-500 hover:text-gray-700 underline">Zrušit filtr</a>
            @endif
        </div>
    </form>
</div>

<div class="space-y-6">
    @forelse($groups as $id => $group)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg border-l-4 border-indigo-500">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-lg leading-6 font-bold text-gray-900">
                        Skupina #{{ $id }} <span class="ml-2 text-sm font-normal text-gray-500">(Subcamp {{ $group['subcamp'] }})</span>
                    </h3>
                </div>
                <div class="flex space-x-2">
                    @foreach($group['stats'] as $country => $data)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $country }}: {{ $data['percentage'] }}% ({{ $data['count'] }})
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Vedoucí</h4>
                        <div class="space-y-1">
                            @forelse($group['leaders'] as $leader)
                                <div class="flex items-center text-sm text-gray-900 bg-green-50 p-2 rounded border border-green-100">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">{{ $leader->first_name }} {{ $leader->last_name }}</span>
                                    <span class="ml-2 text-gray-500">({{ $leader->registration_code }})</span>
                                    <span class="ml-auto text-xs font-bold text-green-700 uppercase tracking-tighter">{{ $leader->country }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-red-500 italic">Bez přiřazeného vedoucího!</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Kódy účastníků ({{ $group['member_count'] }})</h4>
                        <div class="flex flex-wrap gap-1">
                            @foreach($group['codes'] as $code)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-mono bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $code }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white shadow sm:rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Žádné skupiny</h3>
            <p class="mt-1 text-sm text-gray-500">
                Nebyly nalezeny žádné vytvořené skupiny pro daný filtr.
            </p>
        </div>
    @endforelse
</div>
@endsection
