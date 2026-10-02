<x-app-layout>
    <x-slot name="header">Edit Jadwal</x-slot>

    <div class="p-6 max-w-4xl">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.showtimes.show', $showtime) }}"
                class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                transition-all duration-200 shadow-sm">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-14 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0
                border border-gray-200">
                    <img src="{{ $showtime->movie->poster_url }}" class="w-full h-full object-cover"
                        alt="{{ $showtime->movie->title }}">
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Edit Jadwal</h1>
                    <p class="text-sm text-gray-500">
                        {{ $showtime->movie->title }}
                        · {{ $showtime->studio->cinema->name }}
                        · {{ $showtime->start_time->translatedFormat('D, d M Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        @if ($hasOrders)
            <div
                class="mb-5 flex items-start gap-3 bg-amber-50 border border-amber-200
        text-amber-700 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-alert-triangle text-base flex-shrink-0 mt-0.5"></i>
                <p>
                    Jadwal ini sudah memiliki order aktif.
                    Perubahan <strong>waktu</strong> mungkin mempengaruhi pembeli.
                </p>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200
        text-red-700 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-alert-circle flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.showtimes.update', $showtime) }}" class="space-y-5">
            @csrf @method('PUT')

            @include('admin.showtimes._form', [
                'showtime' => $showtime,
                'selectedMovie' => $showtime->movie,
                'selectedCinema' => $showtime->studio->cinema,
            ])

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                    hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                    transition-all duration-200 shadow-sm">
                    <i class="ti ti-check text-base"></i>
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.showtimes.show', $showtime) }}"
                    class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                    font-medium rounded-xl hover:bg-gray-50 transition-all
                    duration-200 flex items-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
