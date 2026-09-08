@php $facility = $facility ?? null; @endphp

<div class="mb-3">
    <label class="block text-sm font-medium">Nama Fasilitas</label>
    <input type="text" name="name" value="{{ old('name', $facility->name ?? '') }}" required
           class="w-full border rounded p-2">
</div>

<div class="mb-3">
    <label class="block text-sm font-medium">Tipe</label>
    <select name="type" required class="w-full border rounded p-2">
        @foreach (['Ruang Kelas', 'Aula', 'Laboratorium', 'Alat', 'Lapangan'] as $type)
            <option value="{{ $type }}" @selected(old('type', $facility->type ?? '') === $type)>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="block text-sm font-medium">Lokasi</label>
    <input type="text" name="location" value="{{ old('location', $facility->location ?? '') }}" required
           class="w-full border rounded p-2">
</div>

<div class="mb-3">
    <label class="block text-sm font-medium">Kapasitas</label>
    <input type="number" name="capacity" min="0" value="{{ old('capacity', $facility->capacity ?? 0) }}" required
           class="w-full border rounded p-2">
</div>

<div class="mb-4">
    <label class="block text-sm font-medium">Deskripsi</label>
    <textarea name="description" class="w-full border rounded p-2">{{ old('description', $facility->description ?? '') }}</textarea>
</div>
