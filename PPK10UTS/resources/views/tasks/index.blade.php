@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-800">Daftar Tugas</h1>
        <a href="{{ route('tasks.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            + Tugas Baru
        </a>
    </div>

    {{-- Filter berdasarkan status, prioritas, kategori --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6 bg-white p-4 rounded-lg border">
        <select name="status" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach (['belum_dikerjakan' => 'Belum Dikerjakan', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="priority" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua Prioritas</option>
            @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $value => $label)
                <option value="{{ $value }}" @selected(request('priority') == $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="category_id" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        @if (request()->hasAny(['status', 'priority', 'category_id']))
            <a href="{{ route('tasks.index') }}" class="text-sm text-gray-500 self-center hover:underline">Reset filter</a>
        @endif
    </form>

    <div class="space-y-3">
        @forelse ($tasks as $task)
            <div class="bg-white border rounded-lg p-4 {{ $task->isOverdue() ? 'border-red-300' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-gray-800 hover:text-indigo-600">
                                {{ $task->title }}
                            </a>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $task->priorityBadge() }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $task->statusBadge() }}">
                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                            </span>
                            @if ($task->category)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">
                                    {{ $task->category->name }}
                                </span>
                            @endif
                            @if ($task->isOverdue())
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-600 text-white">Terlambat</span>
                            @endif
                        </div>

                        @if ($task->deadline)
                            <p class="text-xs text-gray-500 mt-1">
                                Tenggat: {{ $task->deadline->translatedFormat('d M Y') }}
                            </p>
                        @endif

                        {{-- Progress bar + update cepat --}}
                        <form method="POST" action="{{ route('tasks.progress', $task) }}" class="flex items-center gap-2 mt-2">
                            @csrf
                            @method('PATCH')
                            <div class="w-40 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
                            </div>
                            <input type="range" name="progress" min="0" max="100" value="{{ $task->progress }}"
                                   class="w-24" onchange="this.form.submit()">
                            <span class="text-xs text-gray-500">{{ $task->progress }}%</span>
                        </form>
                    </div>

                    <div class="flex gap-2 text-sm">
                        <a href="{{ route('tasks.edit', $task) }}" class="text-gray-500 hover:text-indigo-600">Edit</a>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                              onsubmit="return confirm('Hapus tugas ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 py-10">Belum ada tugas. Tambahkan tugas pertamamu!</p>
        @endforelse
    </div>
@endsection
