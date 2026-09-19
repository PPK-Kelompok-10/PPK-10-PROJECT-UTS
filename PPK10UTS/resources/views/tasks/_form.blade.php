@csrf

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas</label>
    <input type="text" name="title" value="{{ old('title', optional($task)->title) }}"
           class="w-full border rounded-lg px-3 py-2 text-sm" required>
    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Detail / Deskripsi</label>
    <textarea name="description" rows="3"
              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('description', optional($task)->description) }}</textarea>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
        <select name="category_id" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">- Tanpa kategori -</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', optional($task)->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
        <select name="priority" class="w-full border rounded-lg px-3 py-2 text-sm">
            @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', optional($task)->priority ?? 'sedang') == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tenggat Waktu</label>
        <input type="date" name="deadline" value="{{ old('deadline', optional(optional($task)->deadline)->format('Y-m-d')) }}"
               class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
            @foreach (['belum_dikerjakan' => 'Belum Dikerjakan', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', optional($task)->status ?? 'belum_dikerjakan') == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="flex justify-end gap-3 mt-6">
    <a href="{{ route('tasks.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:underline">Batal</a>
    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700">
        Simpan
    </button>
</div>
