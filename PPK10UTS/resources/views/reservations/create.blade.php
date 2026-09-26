<x-app-layout title="Ajukan Reservasi">
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-1">Ajukan Reservasi</h1>
        <p class="text-sm text-gray-500 mb-4">{{ $facility->name }} &middot; {{ $facility->location }}</p>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('reservations.store', $facility) }}" novalidate>
            @csrf

            @php
                // H-1: tanggal paling cepat yang boleh dipilih adalah besok, bukan hari ini.
                $minDate = now()->addDay()->toDateString();

                // Generate pilihan jam kelipatan 30 menit, 07.00-20.00, supaya user TIDAK BISA
                // mengetik/pilih jam sembarangan seperti 10.05 atau 14.12 — ini dropdown, bukan
                // input bebas, jadi otomatis kesalahan format tidak mungkin terjadi dari sisi UI.
                $allSlots = [];
                for ($h = 7; $h <= 20; $h++) {
                    foreach ([0, 30] as $m) {
                        if ($h === 20 && $m === 30) continue; // 20.30 di luar jam operasional
                        $allSlots[] = sprintf('%02d:%02d', $h, $m);
                    }
                }
                // Jam mulai: 07.00 s.d. 19.30 (tidak termasuk 20.00, karena tidak bisa mulai di jam tutup).
                $startOptions = array_slice($allSlots, 0, -1);
                // Jam selesai: 07.30 s.d. 20.00 (tidak termasuk 07.00, karena tidak bisa selesai di jam buka).
                $endOptions = array_slice($allSlots, 1);
            @endphp

            <div class="mb-3">
                <label class="block text-sm font-medium">Tanggal</label>
                <input type="date" name="date" value="{{ old('date', $date) }}" min="{{ $minDate }}" required
                       class="w-full border rounded p-2">
                <p class="text-xs text-gray-400 mt-1">Reservasi wajib diajukan maksimal H-1 (tidak bisa untuk hari ini).</p>
            </div>

            <div class="mb-3 flex gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium">Jam Mulai</label>
                    <select name="start_time" required class="w-full border rounded p-2">
                        <option value="">Pilih jam</option>
                        @foreach ($startOptions as $t)
                            <option value="{{ $t }}" @selected(old('start_time', $startTime) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium">Jam Selesai</label>
                    <select name="end_time" required class="w-full border rounded p-2">
                        <option value="">Pilih jam</option>
                        @foreach ($endOptions as $t)
                            <option value="{{ $t }}" @selected(old('end_time', $endTime) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-3">Jam operasional 07.00-20.00, kelipatan slot 30 menit.</p>

            <div class="mb-4">
                <label class="block text-sm font-medium">Tujuan Penggunaan</label>
                <textarea name="purpose" required minlength="3" class="w-full border rounded p-2"
                          placeholder="Contoh: Rapat organisasi mahasiswa">{{ old('purpose') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white rounded p-2">Ajukan Reservasi</button>
        </form>
    </div>
</x-app-layout>
