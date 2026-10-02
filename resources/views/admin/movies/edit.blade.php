<x-app-layout>
    <x-slot name="header">Edit Film</x-slot>

    <div class="p-6 max-w-3xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.movies.show', $movie) }}"
                class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                transition-all duration-200 shadow-sm">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-14 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0
                border border-gray-200">
                    <img src="{{ $movie->poster_url }}" class="w-full h-full object-cover" alt="{{ $movie->title }}">
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Edit Film</h1>
                    <p class="text-sm text-gray-500 truncate max-w-xs">
                        {{ $movie->title }}
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.movies.update', $movie) }}" enctype="multipart/form-data"
            class="space-y-5">
            @csrf @method('PUT')

            @include('admin.movies._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                    hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                    transition-all duration-200 shadow-sm">
                    <i class="ti ti-check text-base"></i>
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.movies.show', $movie) }}"
                    class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                    font-medium rounded-xl hover:bg-gray-50 transition-all
                    duration-200 flex items-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
