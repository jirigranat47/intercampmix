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
                        <a href="{{ route('admin.db') }}" class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium text-blue-200">🔍 Prohlížet Databázi</a>
                    </div>
                </div>
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

</body>
</html>
