@extends('layout')

@section('title', __('Přístup odepřen'))

@section('content')
<div class="max-w-md mx-auto mt-20 text-center">
    <div class="bg-card shadow-2xl rounded-3xl p-10 border border-theme">
        <div class="text-6xl mb-6">🔒</div>
        <h2 class="text-2xl font-black text-gray-900 mb-4">{{ __('Přístup vyžaduje autorizaci') }}</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">
            {{ __('Pro vstup do aplikace musíte použít unikátní odkaz, který vám zaslal administrátor.') }}
        </p>
        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 text-blue-700 text-sm italic">
            {{ __('Po kliknutí na váš osobní odkaz se přístup automaticky uloží do tohoto prohlížeče.') }}
        </div>
    </div>
</div>
@endsection
