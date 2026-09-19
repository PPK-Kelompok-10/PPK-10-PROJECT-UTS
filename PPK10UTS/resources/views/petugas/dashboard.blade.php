<x-app-layout title="Dashboard Petugas">
    <div class="max-w-3xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-1">Dashboard Petugas</h1>
        <p class="text-sm text-gray-500 mb-6">Selamat datang, {{ auth()->user()->name }}.</p>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white border rounded p-4">
                <div class="text-2xl font-bold">{{ $pendingCount }}</div>
                <div class="text-sm text-gray-500">Reservasi menunggu diproses</div>
            </div>
            <div class="bg-white border rounded p-4">
                <div class="text-2xl font-bold">{{ $approvedTodayCount }}</div>
                <div class="text-sm text-gray-500">Disetujui hari ini</div>
            </div>
        </div>

        <h2 class="font-semibold mb-2">Antrean Reservasi Terbaru</h2>
        <div class="bg-white border rounded divide-y">
            @forelse ($pendingReservations as $r)
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <div class="font-medium">{{ $r->facility->name }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $r->start_time->format('d M Y, H:i') }} - {{ $r->end_time->format('H:i') }}
                            &middot; diajukan oleh {{ $r->user->name }}
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-700 capitalize">{{ $r->status }}</span>
                </div>
            @empty
                <div class="p-4 text-sm text-gray-400">Tidak ada reservasi yang menunggu diproses saat ini.</div>
            @endforelse
        </div>

        <p class="text-xs text-gray-400 mt-4">
            Catatan: fitur setujui/tolak reservasi & pencegahan bentrok jadwal (US 8-10) masih
            dikerjakan — halaman ini sementara hanya menampilkan data, belum bisa memproses.
        </p>
    </div>
</x-app-layout>
