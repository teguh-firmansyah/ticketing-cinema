<x-app-layout>
    <x-slot name="header">Tambah Studio</x-slot>

    <div class="p-6 max-w-3xl">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.studios.index') }}"
                class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                transition-all duration-200 shadow-sm">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Tambah Studio Baru</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Pilih bioskop lalu konfigurasi studio
                </p>
            </div>
        </div>

        @if (session('error'))
            <div
                class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200
        text-red-700 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-alert-circle text-base flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.studios.store') }}" class="space-y-5">
            @csrf

            @include('admin.studios._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                    hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                    transition-all duration-200 shadow-sm">
                    <i class="ti ti-check text-base"></i>
                    Buat Studio
                </button>
                <a href="{{ route('admin.studios.index') }}"
                    class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                    font-medium rounded-xl hover:bg-gray-50 transition-all duration-200
                    flex items-center">
                    Batal
                </a>
            </div>
        </form>

    </div>
</x-app-layout>
