@use('App\Models\Report')

<x-app-layout title="Laporkan Kerusakan">
    <div class="max-w-2xl mx-auto mt-10 p-6">
        <a href="{{ route('reports.index') }}" class="text-sm text-blue-600 hover:underline">Kembali ke laporan saya</a>

        <h1 class="text-xl font-bold mt-4 mb-1">Laporkan Kerusakan Fasilitas</h1>
        <p class="text-sm text-gray-500 mb-6">Jelaskan masalahnya dan sertakan foto supaya petugas bisa langsung menindaklanjuti.</p>

        <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data"
              class="space-y-5 bg-white rounded shadow p-6">
            @csrf

            <div>
                <label for="facility_id" class="block text-sm font-medium mb-1">Fasilitas</label>
                <select id="facility_id" name="facility_id" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">Pilih fasilitas</option>
                    @foreach ($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((int) old('facility_id', $selectedFacilityId) === $facility->id)>
                            {{ $facility->name }} ({{ $facility->location }}){{ $facility->status === 'dalam_perbaikan' ? ', sedang dalam perbaikan' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('facility_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category" class="block text-sm font-medium mb-1">Kategori</label>
                <select id="category" name="category" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium mb-1">Deskripsi kerusakan</label>
                <textarea id="description" name="description" rows="5" required minlength="10" maxlength="2000"
                          placeholder="Contoh: Proyektor tidak menyala sejak pagi, lampu indikator berkedip merah."
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('description') }}</textarea>
                @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="photo" class="block text-sm font-medium mb-1">Foto bukti</label>
                <input id="photo" name="photo" type="file" required
                       accept="{{ collect(Report::PHOTO_MIMES)->map(fn ($ext) => '.' . $ext)->implode(',') }}"
                       class="block w-full text-sm">
                <p class="text-xs text-gray-500 mt-1">
                    Format {{ strtoupper(implode(', ', Report::PHOTO_MIMES)) }}, maksimal {{ Report::PHOTO_MAX_KB / 1024 }} MB.
                </p>
                @error('photo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Kirim laporan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
