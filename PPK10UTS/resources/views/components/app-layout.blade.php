@props(['title' => 'PPK 2026'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Sistem Reservasi & Pelaporan Fasilitas Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow px-6 py-3 flex justify-between items-center">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="font-bold">PPK 2026</a>
            <a href="{{ route('catalog.index') }}" class="text-sm text-gray-600 hover:text-blue-600">Katalog Fasilitas</a>
            @auth
                @if (auth()->user()->isPengguna())
                    <a href="{{ route('reservations.index') }}" class="text-sm text-gray-600 hover:text-blue-600">Reservasi Saya</a>
                @endif
            @endauth
        </div>

        <div class="flex items-center gap-4 text-sm">
            @auth
                <span class="text-gray-600">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-600">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-blue-600 font-medium">Login</a>
                <a href="{{ route('register') }}" class="text-gray-600 hover:text-blue-600">Daftar</a>
            @endauth
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>
</body>
</html>
