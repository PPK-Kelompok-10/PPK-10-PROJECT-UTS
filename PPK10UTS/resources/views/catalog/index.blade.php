<x-app-layout title="Katalog Fasilitas">
    <div class="max-w-5xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-1">Katalog Fasilitas Kampus</h1>
        <p class="text-sm text-gray-500 mb-6">
            Cek ketersediaan fasilitas per slot waktu. @guest Login untuk mengajukan reservasi. @endguest
        </p>

        <form method="GET" class="flex flex-wrap gap-2 mb-6 bg-white p-4 rounded border">
            <select name="type" class="border rounded p-2 text-sm">
                <option value="">Semua Tipe</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <input type="text" name="location" value="{{ request('location') }}" placeholder="Lokasi"
                   class="border rounded p-2 text-sm flex-1 min-w-[140px]">
            <input type="number" name="min_capacity" value="{{ request('min_capacity') }}" min="0"
                   placeholder="Kapasitas min." class="border rounded p-2 text-sm w-36">
            <button class="bg-blue-600 text-white rounded px-4 py-2 text-sm">Cari</button>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($facilities as $facility)
                <a href="{{ route('catalog.show', $facility) }}" class="block bg-white border rounded p-4 hover:shadow-md">
                    <div class="font-semibold">{{ $facility->name }}</div>
                    <div class="text-sm text-gray-500">{{ $facility->type }} &middot; {{ $facility->location }}</div>
                    <div class="text-sm text-gray-500">Kapasitas: {{ $facility->capacity }}</div>
                </a>
            @empty
                <p class="text-gray-400 col-span-full">Tidak ada fasilitas yang cocok dengan pencarian.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $facilities->links() }}</div>
    </div>
</x-app-layout>
