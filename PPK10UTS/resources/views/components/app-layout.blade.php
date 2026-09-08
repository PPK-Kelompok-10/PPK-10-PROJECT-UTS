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
        <span class="font-bold">PPK 2026</span>
        @auth
            <div class="flex items-center gap-4 text-sm">
                <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-600">Logout</button>
                </form>
            </div>
        @endauth
    </nav>

    <main>
        {{ $slot }}
    </main>
</body>
</html>
