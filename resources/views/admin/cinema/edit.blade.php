<x-app-layout>
    <x-slot name="header">Edit Bioskop</x-slot>

    <div class="p-6 w-full">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.cinema.show', $cinema) }}"
                class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                transition-all duration-200 shadow-sm">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900">
                    Edit — {{ $cinema->name }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Perbarui informasi bioskop
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.cinema.update', $cinema) }}" enctype="multipart/form-data"
            class="space-y-5">
            @csrf @method('PUT')

            @include('admin.cinema._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                    hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                    transition-all duration-200 shadow-sm">
                    <i class="ti ti-check text-base"></i>
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.cinema.show', $cinema) }}"
                    class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                    font-medium rounded-xl hover:bg-gray-50 transition-all duration-200
                    flex items-center">
                    Batal
                </a>
            </div>
        </form>

    </div>
</x-app-layout>
