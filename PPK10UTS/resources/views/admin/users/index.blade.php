<x-app-layout>
    <div class="max-w-5xl mx-auto mt-10 p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Manajemen Akun</h1>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white rounded px-4 py-2">
                + Daftarkan Akun (Petugas / Pengguna)
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
        @endif

        <h2 class="font-semibold mb-2">Menunggu Verifikasi (Registrasi Mandiri)</h2>
        <table class="w-full mb-8 border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nama</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pending as $u)
                    <tr class="border-t">
                        <td class="p-2">{{ $u->name }}</td>
                        <td class="p-2">{{ $u->email }}</td>
                        <td class="p-2 flex gap-2">
                            <form method="POST" action="{{ route('admin.users.verify', $u) }}">
                                @csrf @method('PATCH')
                                <button class="text-green-600">Verifikasi</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.reject', $u) }}">
                                @csrf @method('PATCH')
                                <button class="text-red-600">Tolak</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-2 text-gray-400">Tidak ada akun menunggu verifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2 class="font-semibold mb-2">Akun Aktif</h2>
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nama</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($verified as $u)
                    <tr class="border-t">
                        <td class="p-2">{{ $u->name }}</td>
                        <td class="p-2">{{ $u->email }}</td>
                        <td class="p-2 capitalize">{{ $u->role }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
