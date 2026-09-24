<x-app-layout title="Detail Laporan">
    <div class="max-w-3xl mx-auto mt-10 p-6">
        <a href="{{ route('reports.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke laporan saya</a>

        @if (session('status'))
            <div class="mt-4 p-3 rounded bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="mt-4 bg-white rounded shadow p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-xl font-bold">{{ $report->facility->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $report->facility->location }}</p>
                </div>
                <x-report-status :status="$report->status" />
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-6">
                <div>
                    <dt class="text-gray-500">Kategori</dt>
                    <dd class="font-medium">{{ $report->categoryLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Dikirim</dt>
                    <dd class="font-medium">{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <h2 class="font-semibold mb-1">Deskripsi</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line mb-6">{{ $report->description }}</p>

            <h2 class="font-semibold mb-2">Foto bukti</h2>
            @if ($report->photo_path)
                <a href="{{ route('reports.photo', $report) }}" target="_blank" rel="noopener">
                    <img src="{{ route('reports.photo', $report) }}" alt="Foto bukti laporan kerusakan"
                         class="max-h-96 rounded border border-gray-200">
                </a>
            @else
                <p class="text-sm text-gray-500">Tidak ada foto.</p>
            @endif
        </div>

        <div class="mt-4 bg-white rounded shadow p-6">
            <h2 class="font-semibold mb-2">Tindak lanjut petugas</h2>

            @if ($report->status === 'baru')
                <p class="text-sm text-gray-500">Laporan belum ditangani. Petugas akan memprosesnya secepatnya.</p>
            @else
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <dt class="text-gray-500">Ditangani oleh</dt>
                        <dd class="font-medium">{{ $report->handler?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Ditutup pada</dt>
                        <dd class="font-medium">{{ $report->resolved_at?->format('d/m/Y H:i') ?? 'Masih diproses' }}</dd>
                    </div>
                </dl>

                <h3 class="text-sm text-gray-500 mb-1">Catatan resolusi</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->resolution_note ?? 'Belum ada catatan.' }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
