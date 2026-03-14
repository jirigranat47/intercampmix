<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>International Mixer - @yield('title')</title>
    <!-- Tailwind CSS included via CDN for rapid proto, as it fits your requests -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg-primary: #f9fafb;
            --bg-secondary: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --nav-bg: #1e40af;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --input-bg: #ffffff;
            --accent: #2563eb;
        }

        [data-theme='dark'] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --nav-bg: #1e293b;
            --card-bg: #1e293b;
            --border-color: #334155;
            --input-bg: #334155;
            --accent: #38bdf8;
        }

        [data-theme='pink'] {
            --bg-primary: #fdf2f8;
            --bg-secondary: #fce7f3;
            --text-primary: #831843;
            --text-secondary: #9d174d;
            --nav-bg: #be185d;
            --card-bg: #fff1f2;
            --border-color: #fbcfe8;
            --input-bg: #ffffff;
            --accent: #db2777;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s, color 0.3s;
        }

        .bg-card { background-color: var(--card-bg) !important; }
        .bg-secondary { background-color: var(--bg-secondary) !important; }
        .bg-nav { background-color: var(--nav-bg) !important; }
        .text-primary { color: var(--text-primary) !important; }
        .text-secondary { color: var(--text-secondary) !important; }
        .border-theme { border-color: var(--border-color) !important; }
        
        /* Overriding hardcoded tailwind classes */
        [data-theme='dark'] .bg-white, [data-theme='pink'] .bg-white { background-color: var(--card-bg) !important; }
        [data-theme='dark'] .text-gray-900, [data-theme='pink'] .text-gray-900 { color: var(--text-primary) !important; }
        [data-theme='dark'] .text-gray-800, [data-theme='pink'] .text-gray-800 { color: var(--text-primary) !important; }
        [data-theme='dark'] .text-gray-700, [data-theme='pink'] .text-gray-700 { color: var(--text-secondary) !important; }
        [data-theme='dark'] .text-gray-600, [data-theme='pink'] .text-gray-600 { color: var(--text-secondary) !important; }
        [data-theme='dark'] .bg-gray-50, [data-theme='pink'] .bg-gray-50 { background-color: var(--bg-primary) !important; }
        [data-theme='dark'] .bg-gray-100, [data-theme='pink'] .bg-gray-100 { background-color: var(--bg-secondary) !important; }
        [data-theme='dark'] .border-gray-100, [data-theme='pink'] .border-gray-100 { border-color: var(--border-color) !important; }
        [data-theme='dark'] .border-gray-200, [data-theme='pink'] .border-gray-200 { border-color: var(--border-color) !important; }
        [data-theme='dark'] .border-gray-300, [data-theme='pink'] .border-gray-300 { border-color: var(--border-color) !important; }
        [data-theme='dark'] .divide-gray-200 > :not([hidden]) ~ :not([hidden]), [data-theme='pink'] .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--border-color) !important; }

        /* Alerts */
        [data-theme='dark'] .bg-green-100 { background-color: rgba(16, 185, 129, 0.2) !important; color: #34d399 !important; border-color: #065f46 !important; }
        [data-theme='dark'] .bg-red-100 { background-color: rgba(239, 68, 68, 0.2) !important; color: #f87171 !important; border-color: #991b1b !important; }
        [data-theme='dark'] .bg-red-50 { background-color: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; border-color: #7f1d1d !important; }
        [data-theme='pink'] .bg-green-100 { background-color: #fdf2f8 !important; color: #9d174d !important; border-color: #fbcfe8 !important; }

        input, select, textarea { 
            background-color: var(--input-bg) !important; 
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
    </style>
    <script>
        // Theme & Language Initialization
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', savedTheme || systemTheme);

            const savedLang = localStorage.getItem('lang');
            const browserLang = navigator.language.startsWith('cs') ? 'cs' : 'en';
            const currentLang = savedLang || browserLang;
            
            // TOKEN SYNC (localStorage -> Cookie) for PHP backend
            const token = localStorage.getItem('access_token');
            if (token) {
                document.cookie = "access_token=" + token + "; path=/; max-age=" + (60 * 60 * 24 * 365);
            } else if (document.cookie.includes('access_token=')) {
                // If missing in localStorage but present in cookie (e.g. cleared storage), clear cookie too
                document.cookie = "access_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
            }
        })();
    </script>
</head>
<body class="min-h-screen flex flex-col">
    
    <nav class="bg-nav text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-baseline">
                    <a href="/" class="text-xl font-bold tracking-wider">🏕️ Intercamp Mixer</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <div class="flex items-baseline space-x-4">
                        @if(($userRole ?? 'none') !== 'none')
                            <a href="/" class="hover:opacity-80 px-3 py-2 rounded-md text-sm font-medium">{{ __('Vyhledávání') }}</a>
                            
                            @if($userRole === 'admin')
                                <a href="{{ route('admin.import') }}" class="hover:opacity-80 px-3 py-2 rounded-md text-sm font-medium">{{ __('Administrace/Nahrávání') }}</a>
                            @endif
                            
                            <a href="{{ route('admin.db') }}" class="hover:opacity-80 px-3 py-2 rounded-md text-sm font-medium opacity-80">🔍 {{ __('Prohlížet databázi') }}</a>

                            @if($userRole === 'admin')
                                 <a href="{{ route('admin.tokens') }}" class="hover:opacity-80 px-3 py-2 rounded-md text-sm font-medium">🔑 {{ __('Správa Tokenů') }}</a>
                            @endif

                            <a href="{{ route('auth.logout') }}" class="hover:opacity-80 px-3 py-2 rounded-md text-sm font-medium text-red-100">🚪 {{ __('Odhlásit') }}</a>
                        @endif
                    </div>
                </div>
                
                <!-- Theme Switcher -->
                <div class="flex items-center space-x-2">
                    <button onclick="setTheme('light')" class="p-1 rounded-full hover:bg-white/10" title="Light Mode">☀️</button>
                    <button onclick="setTheme('dark')" class="p-1 rounded-full hover:bg-white/10" title="Dark Mode">🌙</button>
                    <button onclick="setTheme('pink')" class="p-1 rounded-full hover:bg-white/10" title="Pink Mode">🌸</button>
                </div>

                @if(($userRole ?? 'none') !== 'none')
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-button" type="button" class="bg-blue-900 inline-flex items-center justify-center p-2 rounded-md text-gray-200 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">{{ __('Otevřít hlavní menu') }}</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. -->
        @if(($userRole ?? 'none') !== 'none')
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">{{ __('Vyhledávání') }}</a>
                @if($userRole === 'admin')
                    <a href="{{ route('admin.import') }}" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">{{ __('Administrace/Nahrávání') }}</a>
                @endif
                <a href="{{ route('admin.db') }}" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">🔍 {{ __('Prohlížet databázi') }}</a>
                @if($userRole === 'admin')
                    <a href="{{ route('admin.tokens') }}" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">🔑 {{ __('Správa Tokenů') }}</a>
                @endif
                <a href="{{ route('auth.logout') }}" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">🚪 {{ __('Odhlásit') }}</a>
            </div>
        </div>
        @endif
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">{{ __('Něco se pokazilo!') }}</strong>
                <ul class="list-disc mt-2 ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-auto py-8 border-t border-gray-200 bg-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center opacity-70 text-sm">
            <div>
                &copy; {{ date('Y') }} {{ __('Intercamp Mixer - Strategické Rozřazování') }}
            </div>
            <div class="mt-4 md:mt-0 flex space-x-6">
                <button onclick="setLanguage('cs')" class="hover:underline font-bold" id="lang-cs">Čeština</button>
                <button onclick="setLanguage('en')" class="hover:underline" id="lang-en">English</button>
            </div>
        </div>
    </footer>

    <script>
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }

        function setLanguage(lang) {
            localStorage.setItem('lang', lang);
            // In a real Laravel app, we would redirect to a route that sets the locale
            // For now, we will reload the page with a lang parameter to trigger backend change
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const svgs = this.querySelectorAll('svg');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                svgs[0].classList.add('hidden');
                svgs[1].classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
                svgs[0].classList.remove('hidden');
                svgs[1].classList.add('hidden');
            }
        });

        // Initialize active language appearance
        const currentLang = localStorage.getItem('lang') || (navigator.language.startsWith('cs') ? 'cs' : 'en');
        document.getElementById('lang-' + currentLang)?.classList.add('text-blue-600', 'underline');
    </script>
</body>

</html>
