<x-app-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">Login</h1>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border rounded p-2">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" required
                       class="w-full border rounded p-2">
            </div>

            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" class="text-sm">Ingat saya</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white rounded p-2">Login</button>
        </form>

        <p class="text-sm mt-4">
            Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600">Daftar</a>
        </p>
    </div>
</x-app-layout>
