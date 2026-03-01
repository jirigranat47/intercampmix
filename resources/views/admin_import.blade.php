@extends('layout')

@section('title', 'Administrace Importu')

@section('content')
<div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-8 max-w-3xl">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Krok 1: Import Dat</h3>
            <p class="mt-1 text-sm text-gray-500">
                Lze nahrát pouze validní <code>.xlsx</code> soubory. Aktuálně existující data se přemažou.
            </p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700">Soubor (Intercamp Groups)</label>
                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Nahrát a Zpracovat Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-8 max-w-3xl">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Krok 2: Spuštění Algoritmu</h3>
            <p class="mt-1 text-sm text-gray-500">
                Po úspěšném nahrání spusťte algoritmus Míchání Skupin. Pozor, tímto se smažou jakékoliv stará přiřazení k cílovým skupinám.
            </p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('admin.mix.process') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Spustit Strategické Rozřazení
                </button>
            </form>
        </div>
    </div>
</div>

<div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-8 max-w-3xl">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Krok 3: Export Dat</h3>
            <p class="mt-1 text-sm text-gray-500">
                Stáhněte si výsledky algoritmu jako tabulku.
            </p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('admin.export.process') }}" method="GET">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Stáhnout Výsledek (CSV)
                </button>
            </form>
        </div>
    </div>
</div>

<div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-8 max-w-3xl">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Statistiky</h3>
            <p class="mt-1 text-sm text-gray-500">
                Zobrazit rozdělení národností v subcampech.
            </p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <a href="{{ route('admin.stats') }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Zobrazit Statistiky Národností
            </a>
        </div>
    </div>
</div>
@endsection
