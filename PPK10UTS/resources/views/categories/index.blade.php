@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold text-gray-800 mb-6">Kategori Tugas</h1>

    <form method="POST" action="{{ route('categories.store') }}"
          class="bg-white border rounded-lg p-4 mb-6 flex flex-wrap items-end gap-3">
        @csrf
        <div class="flex-1 min-w-[160px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
            <input type="text" name="name" class="w-full border rounded-lg px-3 py-2 text-sm" required>
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
            <input type="color" name="color" value="#3b82f6" class="border rounded-lg h-10 w-16">
        </div>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            Tambah
        </button>
    </form>

    <div class="space-y-2">
        @forelse ($categories as $category)
            <div class="bg-white border rounded-lg p-4 flex items-center justify-between">
                <form method="POST" action="{{ route('categories.update', $category) }}" class="flex items-center gap-3 flex-1">
                    @csrf
                    @method('PUT')
                    <span class="w-3 h-3 rounded-full" style="background: {{ $category->color }}"></span>
                    <input type="text" name="name" value="{{ $category->name }}"
                           class="border rounded-lg px-2 py-1 text-sm w-48">
                    <input type="color" name="color" value="{{ $category->color }}" class="border rounded h-8 w-12">
                    <span class="text-xs text-gray-400">{{ $category->tasks_count }} tugas</span>
                    <button class="text-indigo-600 text-sm hover:underline">Simpan</button>
                </form>

                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                      onsubmit="return confirm('Hapus kategori ini? Tugas terkait tidak ikut terhapus.')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-500 text-sm hover:underline">Hapus</button>
                </form>
            </div>
        @empty
            <p class="text-center text-gray-400 py-10">Belum ada kategori. Tambahkan kategori pertamamu di atas.</p>
        @endforelse
    </div>
@endsection
