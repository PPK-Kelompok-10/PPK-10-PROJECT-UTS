<x-app-layout title="Dashboard Petugas">
    <div class="max-w-6xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-1">Dashboard Petugas</h1>
        <p class="text-sm text-gray-500 mb-6">
            Selamat datang, {{ auth()->user()->name }}. Berikut antrean dan laporan yang perlu diproses.
        </p>

        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 text-red-800 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Antrean reservasi (US 8) --}}
        <section class="mb-10">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h2 class="text-lg font-semibold">Antrean reservasi</h2>
                @if ($pendingCount > $pendingReservations->count())
                    <span class="text-sm text-gray-500">
                        Menampilkan {{ $pendingReservations->count() }} dari {{ $pendingCount }} reservasi
                    </span>
                @endif
            </div>

            @if ($pendingReservations->isEmpty())
                <div class="p-6 bg-white rounded shadow text-center text-sm text-gray-500">
                    Tidak ada reservasi yang menunggu persetujuan.
                </div>
            @else
                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-2 font-medium">Jadwal</th>
                                <th class="px-4 py-2 font-medium">Fasilitas</th>
                                <th class="px-4 py-2 font-medium">Pemohon</th>
                                <th class="px-4 py-2 font-medium">Tujuan</th>
                                <th class="px-4 py-2 font-medium">Diajukan</th>
                                <th class="px-4 py-2 font-medium">Status</th>
                                <th class="px-4 py-2 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingReservations as $reservation)
                                <tr class="border-t border-gray-200">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $reservation->start_time->format('d/m/Y') }}
                                        <div class="text-xs text-gray-400">
                                            {{ $reservation->start_time->format('H:i') }}–{{ $reservation->end_time->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $reservation->facility->name }}
                                        <div class="text-xs text-gray-400">{{ $reservation->facility->location }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $reservation->user->name }}</td>
                                    <td class="px-4 py-2">{{ $reservation->purpose }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-500">{{ $reservation->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-2">{{ $reservation->status }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex gap-2">
                                            @foreach (['approve' => 'Approve', 'reject' => 'Reject'] as $action => $label)
                                                <form method="POST" action="{{ route('petugas.reservations.' . $action, $reservation) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700">{{ $label }}</button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="mb-6">{{ $pendingReservations->links() }}</div>

        <section class="mb-10">
            <h2 class="text-lg font-semibold mb-3">Reservasi disetujui</h2>
            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-2">Pemesan / Fasilitas</th>
                            <th class="px-4 py-2">Tujuan / Jadwal</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Pembatalan darurat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($approvedReservations as $reservation)
                            <tr class="border-t border-gray-200">
                                <td class="px-4 py-2">
                                    {{ $reservation->user->name }}
                                    <div class="text-gray-500">{{ $reservation->facility->name }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    {{ $reservation->purpose }}
                                    <div class="text-gray-500">{{ $reservation->start_time->format('d/m/Y H:i') }}–{{ $reservation->end_time->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-2">{{ $reservation->status }}</td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="{{ route('petugas.reservations.cancel', $reservation) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="cancellation_reservation_id" value="{{ $reservation->id }}">
                                        <label for="reason-{{ $reservation->id }}" class="block mb-1">Alasan pembatalan (wajib)</label>
                                        <textarea id="reason-{{ $reservation->id }}" name="reason" required maxlength="500" rows="2" class="w-full border border-gray-300 rounded p-2">{{ (string) old('cancellation_reservation_id') === (string) $reservation->id ? old('reason') : '' }}</textarea>
                                        @if ((string) old('cancellation_reservation_id') === (string) $reservation->id)
                                            @error('reason')
                                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        @endif
                                        <button class="mt-2 px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">Cancel Reservasi</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-gray-500">Tidak ada reservasi disetujui.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $approvedReservations->links() }}</div>
        </section>

        {{-- Laporan kerusakan (US 8 & 11) --}}
        <section>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('petugas.reports.index') }}" class="block p-4 bg-white rounded shadow hover:shadow-md">
                    <div class="flex items-center justify-between gap-2">
                        <div class="font-semibold">Kelola Laporan</div>
                        @if ($openReportsCount > 0)
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs">{{ $openReportsCount }} terbuka</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">Tangani laporan kerusakan fasilitas</div>
                </a>
            </div>
        </section>
    </div>
</x-app-layout>
