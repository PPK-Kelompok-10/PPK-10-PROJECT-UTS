<x-app-layout title="Laporan Kerusakan">
    <div class="max-w-6xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-1">Laporan Kerusakan</h1>
        <p class="text-sm text-gray-500 mb-6">Tangani laporan dari pengguna, mulai dari yang paling lama menunggu.</p>

        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div class="flex flex-wrap gap-2">
                @foreach ($filters as $key => $label)
                    <a href="{{ route('petugas.reports.index', array_filter(['status' => $key, 'category' => $category])) }}"
                       class="px-3 py-1.5 rounded text-sm {{ $filter === $key ? 'bg-blue-600 text-white' : 'bg-white shadow text-gray-700 hover:bg-gray-50' }}">
                        {{ $label }}
                        <span class="ml-1 text-xs {{ $filter === $key ? 'text-blue-100' : 'text-gray-400' }}">{{ $filterCounts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('petugas.reports.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $filter }}">
                <label for="category" class="text-sm text-gray-600">Kategori</label>
                <select id="category" name="category" onchange="this.form.submit()"
                        class="border border-gray-300 rounded px-2 py-1.5 text-sm">
                    <option value="">Semua</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="px-3 py-1.5 bg-gray-200 rounded text-sm">Terapkan</button></noscript>
            </form>
        </div>

        @if ($reports->isEmpty())
            <div class="p-6 bg-white rounded shadow text-center text-sm text-gray-500">
                Tidak ada laporan untuk filter ini.
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
                        @foreach ($reports as $report)
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
                                <td class="px-4 py-2 text-right whitespace-nowrap">
                                    <a href="{{ route('petugas.reports.show', $report) }}" class="text-blue-600 hover:underline">
                                        {{ $report->isOpen() ? 'Tangani' : 'Lihat' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $reports->links() }}</div>
        @endif
    </div>
</x-app-layout>
