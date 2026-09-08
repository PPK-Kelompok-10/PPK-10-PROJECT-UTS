<x-app-layout>
    <div class="max-w-5xl mx-auto mt-10 p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Data Master Fasilitas</h1>
            <a href="{{ route('admin.facilities.create') }}" class="bg-blue-600 text-white rounded px-4 py-2">
                + Tambah Fasilitas
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
        @endif

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Nama</th>
                    <th class="p-2 text-left">Tipe</th>
                    <th class="p-2 text-left">Lokasi</th>
                    <th class="p-2 text-left">Kapasitas</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($facilities as $f)
                    <tr class="border-t">
                        <td class="p-2">{{ $f->name }}</td>
                        <td class="p-2">{{ $f->type }}</td>
                        <td class="p-2">{{ $f->location }}</td>
                        <td class="p-2">{{ $f->capacity }}</td>
                        <td class="p-2 capitalize">{{ str_replace('_', ' ', $f->status) }}</td>
                        <td class="p-2 flex gap-2">
                            <a href="{{ route('admin.facilities.edit', $f) }}" class="text-blue-600">Edit</a>
                            @if ($f->status !== 'nonaktif')
                                <form method="POST" action="{{ route('admin.facilities.disable', $f) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-red-600">Nonaktifkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.facilities.activate', $f) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-green-600">Aktifkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $facilities->links() }}</div>
    </div>
</x-app-layout>
