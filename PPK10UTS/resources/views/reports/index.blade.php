<x-app-layout title="Laporan Kerusakan Saya">
    <div class="max-w-4xl mx-auto mt-10 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold">Laporan Kerusakan Saya</h1>
                <p class="text-sm text-gray-500">Pantau status laporan yang sudah Anda kirim.</p>
            </div>
            <a href="{{ route('reports.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Buat laporan
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        @if ($reports->isEmpty())
            <div class="p-6 bg-white rounded shadow text-center text-sm text-gray-500">
                Belum ada laporan.
            </div>
        @else
            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-2 font-medium">Dikirim</th>
                            <th class="px-4 py-2 font-medium">Fasilitas</th>
                            <th class="px-4 py-2 font-medium">Kategori</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr class="border-t border-gray-200">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2">{{ $report->facility->name }}</td>
                                <td class="px-4 py-2">{{ $report->categoryLabel() }}</td>
                                <td class="px-4 py-2"><x-report-status :status="$report->status" /></td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:underline">Lihat detail</a>
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
