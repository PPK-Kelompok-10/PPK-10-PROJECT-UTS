@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold text-gray-800 mb-6">Edit Tugas</h1>

    <form method="POST" action="{{ route('tasks.update', $task) }}" class="bg-white border rounded-lg p-6 max-w-xl">
        @method('PUT')
        @include('tasks._form')
    </form>
@endsection
