@extends('layout')

@section('title', __('Správa kontaktů skupin'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-black text-gray-900">{{ __('Správa kontaktů') }}</h2>
        <p class="text-gray-500">{{ __('Vyhledejte skupinu pro úpravu kontaktních údajů vedoucího.') }}</p>
    </div>

    <div class="bg-card shadow-xl rounded-3xl p-6 sm:p-8 border border-theme relative overflow-hidden">
        <div>
            <label for="group-search" class="block text-sm font-bold text-gray-700 mb-2">{{ __('Začněte psát název skupiny nebo zemi...') }}</label>
            <div class="relative">
                <input type="text" id="group-search" autocomplete="off" 
                    placeholder="{{ __('Příklad: Troop 123, Germany...') }}"
                    class="w-full px-6 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-lg font-medium">
                
                <div id="autocomplete-results" class="hidden absolute left-0 right-0 z-50 mt-2 bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden max-h-96 overflow-y-auto">
                    <!-- Results will be injected here -->
                </div>
            </div>
        </div>
        
        <!-- Decoration -->
        <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none">
            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16a6.471 6.471 0 0 0 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('group-search');
    const resultsContainer = document.getElementById('autocomplete-results');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.groups.autocomplete') }}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        resultsContainer.innerHTML = data.map(group => `
                            <a href="/admin/groups/${group.id}/edit" class="block px-6 py-4 hover:bg-blue-50 transition-colors border-b border-gray-50 last:border-0 group">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-gray-900 group-hover:text-blue-700">${group.troop_name}</div>
                                        <div class="text-sm text-gray-500">${group.country} <span class="mx-1">•</span> ${group.subcamp}</div>
                                    </div>
                                    <div class="text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </div>
                                </div>
                            </a>
                        `).join('');
                        resultsContainer.classList.remove('hidden');
                    } else {
                        resultsContainer.innerHTML = '<div class="px-6 py-4 text-gray-500 italic">{{ __("Nebyly nalezeny žádné skupiny.") }}</div>';
                        resultsContainer.classList.remove('hidden');
                    }
                });
        }, 300);
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });
});
</script>
@endsection
