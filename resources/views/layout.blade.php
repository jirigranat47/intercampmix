<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>International Mixer - @yield('title')</title>
    <!-- Tailwind CSS included via CDN for rapid proto, as it fits your requests -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-baseline">
                    <a href="/" class="text-xl font-bold tracking-wider">🏕️ Intercamp Mixer</a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/" class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Veřejné hledání</a>
                        <a href="{{ route('admin.import') }}" class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Administrace/Nahrávání</a>
                        <a href="{{ route('admin.db') }}" class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium text-blue-200">🔍 Prohlížet databázi</a>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-button" type="button" class="bg-blue-900 inline-flex items-center justify-center p-2 rounded-md text-gray-200 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Otevřít hlavní menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Veřejné hledání</a>
                <a href="{{ route('admin.import') }}" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Administrace/Nahrávání</a>
                <a href="{{ route('admin.db') }}" class="hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium text-blue-200">🔍 Prohlížet databázi</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Něco se pokazilo!</strong>
                <ul class="list-disc mt-2 ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
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
    </script>
</body>
</html>
