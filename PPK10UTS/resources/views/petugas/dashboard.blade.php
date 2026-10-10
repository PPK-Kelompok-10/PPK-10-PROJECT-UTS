<x-app-layout title="Dashboard Petugas">
    <div class="max-w-6xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-1">Dashboard Petugas</h1>
        <p class="text-sm text-gray-500 mb-6">
            Selamat datang, {{ auth()->user()->name }}. Berikut antrean yang perlu diproses.
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
            <h2 class="text-lg font-semibold mb-3">Reservasi disetujui — pembatalan darurat</h2>
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

        {{-- Antrean laporan kerusakan (US 8 & 11) --}}
        <section>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h2 class="text-lg font-semibold">Antrean laporan kerusakan</h2>
                <a href="{{ route('petugas.reports.index') }}"
                   class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Kelola semua laporan
                </a>
            </div>

            @if ($openReports->isEmpty())
                <div class="p-6 bg-white rounded shadow text-center text-sm text-gray-500">
                    Tidak ada laporan yang perlu ditangani.
                </div>
            @else
                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-2 font-medium">Masuk</th>
                                <th class="px-4 py-2 font-medium">Fasilitas</th>
                                <th class="px-4 py-2 font-medium">Pelapor</th>
                                <th class="px-4 py-2 font-medium">Kategori</th>
                                <th class="px-4 py-2 font-medium">Status</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($openReports as $report)
                                <tr class="border-t border-gray-200">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $report->created_at->format('d/m/Y H:i') }}
                                        <div class="text-xs text-gray-400">{{ $report->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $report->facility->name }}
                                        <div class="text-xs text-gray-400">{{ $report->facility->location }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $report->user->name }}</td>
                                    <td class="px-4 py-2">{{ $report->categoryLabel() }}</td>
                                    <td class="px-4 py-2"><x-report-status :status="$report->status" /></td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('petugas.reports.show', $report) }}"
                                           class="inline-block px-3 py-1.5 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50">
                                            Tangani
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($openReportsCount > $openReports->count())
                    <p class="text-sm text-gray-500 mt-2">
                        Menampilkan {{ $openReports->count() }} laporan terlama dari {{ $openReportsCount }}.
                        Lihat sisanya di halaman kelola laporan.
                    </p>
                @endif
            @endif
        </section>
    </div>
</x-app-layout>
