@extends('layout')

@section('title', 'Statistika národností')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.import') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Zpět na administraci
    </a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Rozdělení národností podle subcampů</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Přehled počtu dětí z jednotlivých zemí v rámci každého subcampu.</p>
    </div>
    <div class="border-t border-gray-200">
        <div class="flex flex-wrap">
            @php
                $subcamps = $stats->groupBy('subcamp');
            @endphp

            @foreach($subcamps as $subcampId => $nationalities)
            <div class="w-full md:w-1/2 p-4">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 h-full">
                    <h4 class="text-md font-bold text-blue-800 mb-3 border-b pb-2 flex justify-between">
                        <span>Subcamp {{ $subcampId }}</span>
                        <span class="text-gray-600 font-normal">Celkem: {{ $subcampTotals[$subcampId] ?? 0 }}</span>
                    </h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Země</th>
                                <th class="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Počet dětí</th>
                                <th class="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Podíl</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($nationalities as $nat)
                            <tr>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-900">{{ $nat->country }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-900 text-right font-medium">{{ $nat->count }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500 text-right">
                                    {{ number_format(($nat->count / $subcampTotals[$subcampId]) * 100, 1) }} %
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
