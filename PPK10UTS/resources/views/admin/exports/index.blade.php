@php
    $period = ['from' => $from->toDateString(), 'to' => $to->toDateString()];
@endphp

<x-app-layout title="Rekap Fasilitas">
    <div class="max-w-7xl mx-auto mt-10 p-6">
        <h1 class="text-xl font-bold mb-1">Rekap Okupansi &amp; Kerusakan Fasilitas</h1>
        <p class="text-sm text-gray-500 mb-6">
            Periode {{ $from->format('d/m/Y') }} sampai {{ $to->format('d/m/Y') }} ({{ $days }} hari).
        </p>

        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <form method="GET" action="{{ route('admin.exports.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="from" class="block text-sm font-medium mb-1">Dari</label>
                    <input id="from" name="from" type="date" value="{{ $from->toDateString() }}"
                           class="border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="to" class="block text-sm font-medium mb-1">Sampai</label>
                    <input id="to" name="to" type="date" value="{{ $to->toDateString() }}"
                           class="border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Tampilkan
                </button>
            </form>

            <div class="flex gap-2">
                <a href="{{ route('admin.exports.csv', $period) }}"
                   class="px-4 py-2 bg-white shadow text-sm rounded hover:bg-gray-50">Unduh CSV</a>
                <a href="{{ route('admin.exports.pdf', $period) }}"
                   class="px-4 py-2 bg-white shadow text-sm rounded hover:bg-gray-50">Unduh PDF</a>
            </div>
        </div>

        @if ($errors->hasAny(['from', 'to']))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-800 text-sm">
                {{ $errors->first('from') ?: $errors->first('to') }}
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Reservasi disetujui</div>
                <div class="text-2xl font-semibold">{{ $summary['approved_reservations'] }}</div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Total jam terpakai</div>
                <div class="text-2xl font-semibold">{{ $summary['booked_hours'] }}</div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Rata-rata okupansi</div>
                <div class="text-2xl font-semibold">{{ $summary['average_occupancy'] }}%</div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Laporan kerusakan</div>
                <div class="text-2xl font-semibold">{{ $summary['reports_total'] }}</div>
                <div class="text-xs text-gray-400">{{ $summary['reports_open'] }} masih terbuka</div>
            </div>
        </div>

        @if ($rows->isEmpty())
            <div class="p-6 bg-white rounded shadow text-center text-sm text-gray-500">Belum ada data fasilitas.</div>
        @else
            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-2 font-medium">Fasilitas</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2 font-medium text-right">Reservasi</th>
                            <th class="px-4 py-2 font-medium text-right">Jam terpakai</th>
                            <th class="px-4 py-2 font-medium w-40">Okupansi</th>
                            <th class="px-4 py-2 font-medium text-right">Laporan</th>
                            <th class="px-4 py-2 font-medium text-right">Selesai</th>
                            <th class="px-4 py-2 font-medium text-right">Terbuka</th>
                            <th class="px-4 py-2 font-medium">Kategori terbanyak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr class="border-t border-gray-200">
                                <td class="px-4 py-2">
                                    {{ $row['name'] }}
                                    <div class="text-xs text-gray-400">{{ $row['type'] }}, {{ $row['location'] }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $row['status_label'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['approved_reservations'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['booked_hours'] }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded">
                                            <div class="h-1.5 bg-blue-600 rounded" style="width: {{ min(100, $row['occupancy_percent']) }}%"></div>
                                        </div>
                                        <span class="w-12 text-right">{{ $row['occupancy_percent'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right">{{ $row['reports_total'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['reports_resolved'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['reports_open'] }}</td>
                                <td class="px-4 py-2">{{ $row['top_category'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
