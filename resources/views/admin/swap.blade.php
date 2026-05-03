@extends('layout')

@section('title', __('Manuální přesun účastníka'))

@section('content')
<div class="max-w-5xl mx-auto space-y-12 pb-12">
    <div>
        <h1 class="text-4xl font-black text-gray-900 tracking-tight">{{ __('Manuální přesun účastníka') }}</h1>
        <p class="text-lg text-gray-500 mt-2">{{ __('Vyhledejte účastníka a přesměrujte jej do jiné cílové skupiny.') }}</p>
    </div>

    @if($errors->any())
        <div class="p-6 bg-red-50 border-l-8 border-red-500 text-red-900 rounded-3xl shadow-sm">
            <div class="font-bold text-xl mb-2">{{ __('Něco se pokazilo!') }}</div>
            <ul class="list-disc ml-5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="p-6 bg-green-50 border-l-8 border-green-500 text-green-900 rounded-3xl shadow-sm animate-bounce">
            <div class="font-bold text-xl mb-1">{{ __('Úspěch!') }}</div>
            <p class="font-medium font-lg">{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('admin.swap.process') }}" method="POST" id="swap-form" onsubmit="return confirm('{{ __('Opravdu chcete tohoto účastníka přesunout? Tato akce okamžitě změní jeho cílovou skupinu a registrační kód.') }}')">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 relative">
            <!-- Center Decoration -->
            <div class="hidden lg:flex absolute inset-0 items-center justify-center pointer-events-none">
                <div class="w-16 h-16 bg-white rounded-full border-4 border-gray-100 shadow-xl flex items-center justify-center text-3xl z-10">
                    ➡️
                </div>
            </div>

            <!-- Participant 1 -->
            <div class="space-y-6">
                <div class="bg-card shadow-2xl rounded-[2.5rem] p-8 border border-theme relative">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-blue-500/5 rounded-full"></div>
                    
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">{{ __('Účastník k přesunu') }}</label>
                    <div class="relative">
                        <input type="text" id="p1-search" autocomplete="off"
                            placeholder="{{ __('Kód, jméno nebo příjmení...') }}"
                            class="w-full px-6 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-lg font-bold">
                        <div id="p1-results" class="hidden absolute left-0 right-0 z-50 mt-2 bg-white border border-gray-200 rounded-2xl shadow-3xl max-h-72 overflow-y-auto"></div>
                    </div>
                    
                    <input type="hidden" name="p1_id" id="p1-id">
                    
                    <div id="p1-info" class="mt-8 p-6 bg-secondary rounded-3xl border border-theme hidden transform transition-all">
                        <!-- Details will be injected -->
                    </div>
                </div>
            </div>

            <!-- Target Group -->
            <div class="space-y-6">
                <div class="bg-card shadow-2xl rounded-[2.5rem] p-8 border border-theme relative">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-green-500/5 rounded-full"></div>

                    <label class="block text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">{{ __('Cílová skupina') }}</label>
                    <div class="relative">
                        <input type="text" name="target_group" id="target-group" list="available-groups" autocomplete="off"
                            placeholder="{{ __('např. S1-05') }}"
                            class="w-full px-6 py-4 rounded-2xl border-2 border-gray-100 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 transition-all text-lg font-bold">
                        
                        <datalist id="available-groups">
                            @if(isset($targetGroups))
                                @foreach($targetGroups as $g)
                                    <option value="{{ $g }}"></option>
                                @endforeach
                            @endif
                        </datalist>
                    </div>

                    <div class="mt-8 p-6 bg-secondary rounded-3xl border border-theme text-sm text-gray-500">
                        {{ __('Zadejte nebo vyberte existující cílovou skupinu, do které chcete vybraného účastníka přesunout.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-16 flex justify-center">
            <button type="submit" id="submit-btn" disabled
                class="group px-16 py-6 bg-blue-800 text-white rounded-3xl font-black text-2xl shadow-[0_20px_50px_rgba(30,58,138,0.3)] hover:bg-blue-900 transition-all hover:scale-105 active:scale-95 disabled:opacity-30 disabled:pointer-events-none disabled:grayscale">
                <span class="inline-block transition-transform group-hover:translate-x-2 duration-500 mr-2">🚀</span>
                {{ __('Přesunout účastníka') }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setupAutocomplete('p1');
    
    const targetGroupInput = document.getElementById('target-group');
    targetGroupInput.addEventListener('input', checkValidity);

    function setupAutocomplete(prefix) {
        const input = document.getElementById(`${prefix}-search`);
        const results = document.getElementById(`${prefix}-results`);
        const info = document.getElementById(`${prefix}-info`);
        const idInput = document.getElementById(`${prefix}-id`);
        let timer;

        input.addEventListener('input', function() {
            clearTimeout(timer);
            const query = this.value.trim();
            if (query.length < 2) {
                results.classList.add('hidden');
                return;
            }

            timer = setTimeout(() => {
                fetch(`{{ route('admin.swap.search') }}?query=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.length > 0) {
                            results.innerHTML = data.map(p => `
                                <div onclick="selectParticipant('${prefix}', ${JSON.stringify(p).replace(/"/g, '&quot;')})" 
                                     class="px-6 py-4 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-0 group transition-colors">
                                    <div class="font-bold text-gray-900 group-hover:text-blue-700">${p.name}</div>
                                    <div class="text-sm text-gray-500 font-mono tracking-tight">${p.code} <span class="mx-1">•</span> ${p.troop}</div>
                                </div>
                            `).join('');
                            results.classList.remove('hidden');
                        } else {
                            results.innerHTML = '<div class="px-6 py-4 text-gray-400 italic text-sm">{{ __("Žádní účastníci nenalezeni.") }}</div>';
                            results.classList.remove('hidden');
                        }
                    });
            }, 250);
        });

        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.classList.add('hidden');
            }
        });
    }

    window.selectParticipant = function(prefix, p) {
        const input = document.getElementById(`${prefix}-search`);
        const results = document.getElementById(`${prefix}-results`);
        const info = document.getElementById(`${prefix}-info`);
        const idInput = document.getElementById(`${prefix}-id`);

        input.value = p.name;
        idInput.value = p.id;
        results.classList.add('hidden');

        info.innerHTML = `
            <div class="space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-[10px] font-black text-secondary uppercase tracking-widest mb-1">{{ __('Aktuální kód') }}</div>
                        <div class="text-xl font-mono font-black text-blue-500">${p.code}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-secondary uppercase tracking-widest mb-1">{{ __('Subcamp') }}</div>
                        <span class="px-3 py-1 bg-gray-900 text-white rounded-lg font-bold text-xs border border-white/20">SC ${p.subcamp}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-theme">
                    <div>
                        <div class="text-[10px] font-black text-secondary uppercase tracking-widest mb-1">{{ __('Cílová skupina') }}</div>
                        <div class="font-bold text-primary">${p.group}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-secondary uppercase tracking-widest mb-1">{{ __('Země') }}</div>
                        <div class="font-bold text-primary uppercase tracking-wide">${p.country}</div>
                    </div>
                </div>

                <div class="pt-4 border-t border-theme">
                    <div class="text-[10px] font-black text-secondary uppercase tracking-widest mb-1">{{ __('Originální Troop') }}</div>
                    <div class="font-medium text-secondary truncate">${p.troop}</div>
                </div>
            </div>
        `;
        info.classList.remove('hidden');
        checkValidity();
    };

    function checkValidity() {
        const p1Id = document.getElementById('p1-id').value;
        const targetGroup = document.getElementById('target-group').value.trim();
        const btn = document.getElementById('submit-btn');

        if (p1Id && targetGroup.length > 0) {
            btn.disabled = false;
        } else {
            btn.disabled = true;
        }
    }
});
</script>
@endsection
