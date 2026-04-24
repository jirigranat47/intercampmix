@extends('layout')

@section('title', __('Nápověda'))

@section('content')
<div class="max-w-4xl mx-auto space-y-12 pb-12">
    <!-- Header Section -->
    <div class="text-center space-y-4">
        <h1 class="text-4xl font-black tracking-tight text-gray-900">{{ __('Nápověda k aplikaci') }}</h1>
        <p class="text-lg text-gray-500 max-w-2xl mx-auto">
            {{ __('Vítejte v nápovědě k aplikaci Intercamp Mixer. Zde najdete přehled všech funkcí a oprávnění jednotlivých uživatelských rolí.') }}
        </p>
    </div>

    <!-- Roles Section -->
    <section class="space-y-6">
        <div class="flex items-center space-x-3 border-b border-theme pb-2">
            <span class="text-2xl">👥</span>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Uživatelské role') }}</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">            
            <!-- Admin -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm border-l-4 border-l-blue-500">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-lg text-blue-600">{{ __('Administrátor (Admin)') }}</h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-600 text-xs font-bold rounded-full uppercase">Admin</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed">{{ __('role_admin_desc') }}</p>
            </div>
            <!-- Subcamp Chief -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm border-l-4 border-l-green-500">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-lg text-green-600">{{ __('Subcamp Chief') }}</h3>
                    <span class="px-3 py-1 bg-green-100 text-green-600 text-xs font-bold rounded-full uppercase">Chief</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed">{{ __('role_chief_desc') }}</p>
            </div>
            <!-- Viewer -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm border-l-4 border-l-purple-500">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-lg text-purple-600">{{ __('Prohlížení (Viewer)') }}</h3>
                    <span class="px-3 py-1 bg-purple-100 text-purple-600 text-xs font-bold rounded-full uppercase">Viewer</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed">{{ __('role_viewer_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="space-y-6">
        <div class="flex items-center space-x-3 border-b border-theme pb-2">
            <span class="text-2xl">🛠️</span>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Funkce a ovládání') }}</h2>
        </div>
        <div class="space-y-4">
            <!-- Feature 1 -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl flex items-center gap-2">🔍 {{ __('feat_search_title') }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ __('feat_search_desc') }}</p>
                    </div>
                    <div class="shrink-0">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">{{ __('Povoleno pro:') }}</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-md uppercase">Admin</span>
                            <span class="px-2 py-1 bg-green-100 text-green-600 text-[10px] font-bold rounded-md uppercase">Chief</span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-600 text-[10px] font-bold rounded-md uppercase">Viewer</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Feature 2 -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl flex items-center gap-2">📊 {{ __('feat_db_title') }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ __('feat_db_desc') }}</p>
                    </div>
                    <div class="shrink-0">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">{{ __('Povoleno pro:') }}</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-md uppercase">Admin</span>
                            <span class="px-2 py-1 bg-green-100 text-green-600 text-[10px] font-bold rounded-md uppercase">Chief</span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-600 text-[10px] font-bold rounded-md uppercase">Viewer</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Feature 3 -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl flex items-center gap-2">📇 {{ __('feat_manage_title') }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ __('feat_manage_desc') }}</p>
                    </div>
                    <div class="shrink-0">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">{{ __('Povoleno pro:') }}</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-md uppercase">Admin</span>
                            <span class="px-2 py-1 bg-green-100 text-green-600 text-[10px] font-bold rounded-md uppercase">Chief</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Feature 4 -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl flex items-center gap-2">⚙️ {{ __('feat_import_title') }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ __('feat_import_desc') }}</p>
                    </div>
                    <div class="shrink-0">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">{{ __('Povoleno pro:') }}</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-md uppercase">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Feature 5 -->
            <div class="bg-card p-6 rounded-2xl border border-theme shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="font-bold text-xl flex items-center gap-2">🔑 {{ __('feat_tokens_title') }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ __('feat_tokens_desc') }}</p>
                    </div>
                    <div class="shrink-0">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">{{ __('Povoleno pro:') }}</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-md uppercase">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Help Note -->
    <div class="bg-blue-50 border border-blue-100 p-8 rounded-3xl text-center space-y-4">
        <h3 class="text-xl font-bold text-blue-900">{{ __('Potřebujete další pomoc?') }}</h3>
        <p class="text-blue-700/80 text-sm max-w-lg mx-auto">
            {{ __('Pokud narazíte na jakékoli problémy nebo máte dotazy k systému, kontaktujte prosím hlavního administrátora nebo tým technické podpory Intercampu.') }}
        </p>
    </div>
</div>
@endsection
