@extends('layouts.app')

@section('content')
    <a href="{{ route('tasks.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali</a>

    <div class="bg-white border rounded-lg p-6 mt-4 max-w-xl">
        <div class="flex items-center gap-2 flex-wrap mb-2">
            <h1 class="text-xl font-bold text-gray-800">{{ $task->title }}</h1>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $task->priorityBadge() }}">
                {{ ucfirst($task->priority) }}
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $task->statusBadge() }}">
                {{ str_replace('_', ' ', ucfirst($task->status)) }}
            </span>
        </div>

        @if ($task->category)
            <p class="text-sm text-gray-500 mb-2">Kategori: {{ $task->category->name }}</p>
        @endif

        @if ($task->deadline)
            <p class="text-sm text-gray-500 mb-2">Tenggat: {{ $task->deadline->translatedFormat('d M Y') }}</p>
        @endif

        <p class="text-gray-700 mt-4 whitespace-pre-line">{{ $task->description ?: '-' }}</p>

        <div class="mt-6">
            <p class="text-sm text-gray-500 mb-1">Progres: {{ $task->progress }}%</p>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 text-sm hover:underline">Edit</a>
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Hapus tugas ini?')">
                @csrf
                @method('DELETE')
                <button class="text-red-500 text-sm hover:underline">Hapus</button>
            </form>
        </div>
    </div>
@endsection
