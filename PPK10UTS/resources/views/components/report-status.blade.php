@props(['status'])

@php
    $styles = [
        'baru' => 'bg-blue-100 text-blue-800',
        'diproses' => 'bg-amber-100 text-amber-800',
        'selesai' => 'bg-green-100 text-green-800',
        'ditolak' => 'bg-red-100 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-block px-2 py-0.5 rounded text-xs font-medium ' . ($styles[$status] ?? 'bg-gray-100 text-gray-700')]) }}>
    {{ \App\Models\Report::STATUSES[$status] ?? $status }}
</span>
