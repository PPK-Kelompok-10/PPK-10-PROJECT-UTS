@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold text-gray-800 mb-6">Tambah Tugas Baru</h1>

    <form method="POST" action="{{ route('tasks.store') }}" class="bg-white border rounded-lg p-6 max-w-xl">
        @include('tasks._form', ['task' => null])
    </form>
@endsection
