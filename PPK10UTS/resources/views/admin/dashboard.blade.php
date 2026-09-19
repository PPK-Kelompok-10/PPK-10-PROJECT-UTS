<x-app-layout title="Admin Dashboard">
    <div class="max-w-3xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-2">Dashboard Admin</h1>
        <p class="text-sm text-gray-500 mb-6">
            Selamat datang, {{ auth()->user()->name }}.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('admin.users.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                <div class="font-semibold">Manajemen Akun</div>
                <div class="text-sm text-gray-500">Daftarkan Petugas/Pengguna & verifikasi registrasi mandiri</div>
            </a>

            <a href="{{ route('admin.facilities.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                <div class="font-semibold">Data Master Fasilitas</div>
                <div class="text-sm text-gray-500">Kelola ruang kelas, aula, lab, alat, dan lapangan</div>
            </a>

            <a href="{{ route('catalog.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                <div class="font-semibold">Lihat Katalog Publik</div>
                <div class="text-sm text-gray-500">Cek tampilan katalog fasilitas seperti yang dilihat Pengunjung</div>
            </a>
        </div>
    </div>
</x-app-layout>
