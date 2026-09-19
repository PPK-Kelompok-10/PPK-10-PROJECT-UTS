<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jara - Manajemen Tugas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white border-b shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('tasks.index') }}" class="font-bold text-lg text-indigo-600">Jara</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('tasks.index') }}" class="text-gray-600 hover:text-indigo-600">Tugas</a>
                <a href="{{ route('categories.index') }}" class="text-gray-600 hover:text-indigo-600">Kategori</a>
                @auth
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-red-500 hover:underline">Keluar</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-6">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-2 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
