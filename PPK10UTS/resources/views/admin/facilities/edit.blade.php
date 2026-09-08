<x-app-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">Edit Fasilitas</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.facilities.update', $facility) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.facilities._form', ['facility' => $facility])
            <button type="submit" class="w-full bg-blue-600 text-white rounded p-2">Perbarui</button>
        </form>
    </div>
</x-app-layout>
