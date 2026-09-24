@php
    $facility = $report->facility;
    $facilityStatusLabel = [
        'aktif' => 'Aktif',
        'dalam_perbaikan' => 'Dalam perbaikan',
        'nonaktif' => 'Nonaktif',
    ][$facility->status] ?? $facility->status;
@endphp

<x-app-layout title="Tangani Laporan">
    <div class="max-w-6xl mx-auto mt-10 p-6">
        <a href="{{ route('petugas.reports.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke daftar laporan</a>

        @if (session('status'))
            <div class="mt-4 p-3 rounded bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Detail laporan --}}
            <div class="lg:col-span-2 bg-white rounded shadow p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h1 class="text-xl font-bold">{{ $facility->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $facility->location }}, {{ $facility->type }}</p>
                    </div>
                    <x-report-status :status="$report->status" />
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-6">
                    <div>
                        <dt class="text-gray-500">Pelapor</dt>
                        <dd class="font-medium">{{ $report->user->name }}</dd>
                        <dd class="text-xs text-gray-400">{{ $report->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Kategori</dt>
                        <dd class="font-medium">{{ $report->categoryLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Masuk</dt>
                        <dd class="font-medium">{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                        <dd class="text-xs text-gray-400">{{ $report->created_at->diffForHumans() }}</dd>
                    </div>
                </dl>

                <h2 class="font-semibold mb-1">Deskripsi</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line mb-6">{{ $report->description }}</p>

                <h2 class="font-semibold mb-2">Foto bukti</h2>
                @if ($report->photo_path)
                    <a href="{{ route('reports.photo', $report) }}" target="_blank" rel="noopener">
                        <img src="{{ route('reports.photo', $report) }}" alt="Foto bukti laporan kerusakan"
                             class="max-h-[28rem] rounded border border-gray-200">
                    </a>
                @else
                    <p class="text-sm text-gray-500">Tidak ada foto.</p>
                @endif
            </div>

            <div class="space-y-6">
                {{-- US 11: status laporan --}}
                <div class="bg-white rounded shadow p-5">
                    <h2 class="font-semibold mb-3">Status laporan</h2>

                    @if ($report->isOpen())
                        <form method="POST" action="{{ route('petugas.reports.update', $report) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="status" class="block text-sm font-medium mb-1">Ubah menjadi</label>
                                <select id="status" name="status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    @foreach ($report->allowedNextStatuses() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="resolution_note" class="block text-sm font-medium mb-1">Catatan resolusi</label>
                                <textarea id="resolution_note" name="resolution_note" rows="4" maxlength="2000"
                                          placeholder="Contoh: Kabel proyektor diganti, sudah dicoba dan berfungsi normal."
                                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('resolution_note', $report->resolution_note) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Wajib diisi jika laporan diselesaikan atau ditolak.</p>
                                @error('resolution_note') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Simpan status
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-600">
                            {{ $report->statusLabel() }} pada {{ $report->resolved_at?->format('d/m/Y H:i') ?? '-' }}
                            oleh {{ $report->handler?->name ?? '-' }}.
                        </p>
                        <h3 class="text-sm text-gray-500 mt-3 mb-1">Catatan resolusi</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->resolution_note ?? '-' }}</p>
                    @endif
                </div>

                {{-- US 12: status fasilitas --}}
                <div class="bg-white rounded shadow p-5">
                    <h2 class="font-semibold mb-1">Status fasilitas</h2>
                    <p class="text-sm mb-3">Saat ini: <span class="font-medium">{{ $facilityStatusLabel }}</span></p>

                    @error('facility') <p class="text-sm text-red-600 mb-3">{{ $message }}</p> @enderror

                    @if ($facility->status === 'aktif' && $report->isOpen())
                        <form method="POST" action="{{ route('petugas.reports.facility.repair', $report) }}"
                              onsubmit="return confirm('Tandai fasilitas ini dalam perbaikan? Fasilitas tidak bisa direservasi sampai diaktifkan kembali.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full px-4 py-2 bg-amber-500 text-white text-sm rounded hover:bg-amber-600">
                                Tandai dalam perbaikan
                            </button>
                        </form>

                        @if ($upcomingReservations > 0)
                            <p class="text-xs text-gray-500 mt-2">
                                Ada {{ $upcomingReservations }} reservasi mendatang (menunggu atau disetujui) di fasilitas ini.
                                Reservasi yang terdampak bisa dibatalkan lewat menu pembatalan reservasi.
                            </p>
                        @endif
                    @elseif ($facility->status === 'dalam_perbaikan')
                        @if ($report->status === 'selesai')
                            <form method="POST" action="{{ route('petugas.reports.facility.activate', $report) }}"
                                  onsubmit="return confirm('Kembalikan fasilitas ini menjadi aktif?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Kembalikan ke aktif
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-gray-500">Fasilitas bisa dikembalikan ke aktif setelah laporan ini ditandai selesai.</p>
                        @endif

                        @if ($otherOpenReports > 0)
                            <p class="text-xs text-amber-700 mt-2">
                                Masih ada {{ $otherOpenReports }} laporan lain yang terbuka untuk fasilitas ini.
                            </p>
                        @endif
                    @elseif ($facility->status === 'nonaktif')
                        <p class="text-xs text-gray-500">Fasilitas sedang dinonaktifkan Admin, statusnya tidak bisa diubah dari sini.</p>
                    @else
                        <p class="text-xs text-gray-500">Tidak ada tindakan untuk fasilitas dari laporan ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
