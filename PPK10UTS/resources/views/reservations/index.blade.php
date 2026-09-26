<x-app-layout title="Riwayat Reservasi">
    <div class="max-w-3xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-6">Riwayat Reservasi Saya</h1>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
        @endif

        <div class="flex flex-col gap-3">
            @forelse ($reservations as $r)
                <div class="bg-white border rounded p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold">{{ $r->facility->name }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $r->start_time->format('d M Y, H:i') }} - {{ $r->end_time->format('H:i') }}
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Tujuan: {{ $r->purpose }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded capitalize
                            {{ match($r->status) {
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-100 text-gray-500',
                            } }}">
                            {{ $r->status }}
                        </span>
                    </div>

                    @if ($r->canBeCancelledBy(auth()->user()))
                        <form method="POST" action="{{ route('reservations.cancel', $r) }}" class="mt-3 inline"
                              onsubmit="return confirm('Batalkan reservasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-600">Batalkan Reservasi</button>
                        </form>
                    @endif

                    <a href="{{ route('catalog.show', ['facility' => $r->facility, 'date' => $r->start_time->toDateString()]) }}"
                       class="text-sm text-blue-600 mt-3 inline-block ml-3">
                        Lihat ketersediaan fasilitas ini
                    </a>
                </div>
            @empty
                <p class="text-gray-400">Belum ada reservasi. <a href="{{ route('catalog.index') }}" class="text-blue-600">Cari fasilitas</a>.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $reservations->links() }}</div>
    </div>
</x-app-layout>
