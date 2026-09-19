<x-app-layout :title="$facility->name">
    <div class="max-w-3xl mx-auto mt-10 p-6">
        <a href="{{ route('catalog.index') }}" class="text-sm text-blue-600">&larr; Kembali ke katalog</a>

        <h1 class="text-xl font-bold mt-2">{{ $facility->name }}</h1>
        <p class="text-sm text-gray-500 mb-1">{{ $facility->type }} &middot; {{ $facility->location }} &middot; Kapasitas {{ $facility->capacity }}</p>
        @if ($facility->description)
            <p class="text-sm text-gray-600 mb-4">{{ $facility->description }}</p>
        @endif

        <form method="GET" class="flex items-center gap-2 mb-4">
            <label class="text-sm">Tanggal:</label>
            <input type="date" name="date" value="{{ $date->toDateString() }}" min="{{ now()->toDateString() }}"
                   class="border rounded p-2 text-sm" onchange="this.form.submit()">
        </form>

        <p class="text-xs text-gray-400 mb-3">
            Slot waktu tersedia/tidak tersedia. @guest Detail pemohon & tujuan penggunaan tidak ditampilkan untuk publik. @endguest
        </p>

        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            @foreach ($slots as $slot)
                @php
                    $canReserve = $slot['available'] && auth()->check() && auth()->user()->isPengguna() && $facility->status === 'aktif';
                @endphp
                @if ($canReserve)
                    <a href="{{ route('reservations.create', ['facility' => $facility, 'date' => $date->toDateString(), 'start' => $slot['start'], 'end' => $slot['end']]) }}"
                       class="text-center text-sm border rounded py-2 bg-green-50 border-green-300 text-green-700 hover:bg-green-100">
                        {{ $slot['start'] }}
                    </a>
                @else
                    <div class="text-center text-sm border rounded py-2
                        {{ $slot['available'] ? 'bg-green-50 border-green-200 text-green-600' : 'bg-gray-100 border-gray-200 text-gray-400' }}">
                        {{ $slot['start'] }}
                    </div>
                @endif
            @endforeach
        </div>

        <div class="flex gap-4 text-xs text-gray-500 mt-3">
            <div class="flex items-center gap-1"><span class="w-3 h-3 bg-green-50 border border-green-300 inline-block"></span> Tersedia</div>
            <div class="flex items-center gap-1"><span class="w-3 h-3 bg-gray-100 border border-gray-200 inline-block"></span> Tidak tersedia</div>
        </div>

        @guest
            <p class="text-sm mt-6">
                <a href="{{ route('login') }}" class="text-blue-600">Login</a> untuk mengajukan reservasi pada slot yang tersedia.
            </p>
        @endguest
    </div>
</x-app-layout>
