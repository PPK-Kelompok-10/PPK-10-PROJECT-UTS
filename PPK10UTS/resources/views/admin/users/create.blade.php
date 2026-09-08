<x-app-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">Daftarkan Akun Baru</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label class="block text-sm font-medium">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded p-2">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded p-2">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium">Role</label>
                <select name="role" required class="w-full border rounded p-2">
                    <option value="petugas">Petugas</option>
                    <option value="pengguna">Pengguna (Mahasiswa/Dosen/Staf)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" required minlength="8" class="w-full border rounded p-2">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required minlength="8" class="w-full border rounded p-2">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white rounded p-2">Simpan</button>
        </form>
    </div>
</x-app-layout>
