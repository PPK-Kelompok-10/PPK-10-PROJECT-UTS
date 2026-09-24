<x-app-layout title="Dashboard">
    <div class="max-w-3xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-2">Dashboard</h1>
        <p class="text-sm text-gray-500 mb-6">
            Selamat datang, {{ auth()->user()->name }} ({{ auth()->user()->role }}).
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('catalog.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                <div class="font-semibold">Cari Fasilitas</div>
                <div class="text-sm text-gray-500">Lihat katalog & ajukan reservasi</div>
            </a>

            <a href="{{ route('reservations.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                <div class="font-semibold">Reservasi Saya</div>
                <div class="text-sm text-gray-500">Lihat riwayat & status reservasi</div>
            </a>

            <a href="{{ route('reports.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                <div class="font-semibold">Laporan Kerusakan</div>
                <div class="text-sm text-gray-500">Laporkan & pantau kerusakan fasilitas</div>
            </a>
        </div>
    </div>
</x-app-layout>
