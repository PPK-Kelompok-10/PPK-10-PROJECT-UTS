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

            <div class="mb-3">
                <label class="block text-sm font-medium">Tanggal</label>
                <input type="date" name="date" value="{{ old('date', $date) }}" min="{{ now()->toDateString() }}" required
                       class="w-full border rounded p-2">
            </div>

            <div class="mb-3 flex gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium">Jam Mulai</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $startTime) }}" step="1800" required
                           class="w-full border rounded p-2">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium">Jam Selesai</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $endTime) }}" step="1800" required
                           class="w-full border rounded p-2">
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
