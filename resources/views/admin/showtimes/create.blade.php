<x-app-layout>
    <x-slot name="header">Tambah Jadwal Tayang</x-slot>

    <div class="p-6 max-w-4xl" x-data="{ mode: '{{ request('bulk') ? 'bulk' : 'single' }}' }">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.showtimes.index') }}"
                class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                transition-all duration-200 shadow-sm">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Tambah Jadwal Tayang</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Input satu jadwal atau bulk untuk beberapa tanggal sekaligus
                </p>
            </div>
        </div>

        {{-- Flash --}}
        @if (session('error'))
            <div
                class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200
        text-red-700 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-alert-circle flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200
        text-emerald-700 text-sm px-4 py-3 rounded-xl"
                x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <i class="ti ti-circle-check flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Mode toggle --}}
        <div class="flex items-center bg-gray-100 rounded-2xl p-1 mb-6 w-fit">
            <button @click="mode = 'single'"
                :class="mode === 'single'
                    ?
                    'bg-white text-gray-900 shadow-sm' :
                    'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm
                font-semibold transition-all duration-200">
                <i class="ti ti-calendar-plus text-base"></i>
                Satu Jadwal
            </button>
            <button @click="mode = 'bulk'"
                :class="mode === 'bulk'
                    ?
                    'bg-white text-gray-900 shadow-sm' :
                    'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm
                font-semibold transition-all duration-200">
                <i class="ti ti-copy text-base"></i>
                Bulk Input
            </button>
        </div>

        {{-- Single Mode --}}
        <div x-show="mode === 'single'">
            <form method="POST" action="{{ route('admin.showtimes.store') }}" class="space-y-5">
                @csrf
                @include('admin.showtimes._form', [
                    'showtime' => new \App\Models\Showtime(),
                    'selectedMovie' => $selectedMovie,
                    'selectedCinema' => $selectedCinema,
                ])
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                        hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                        transition-all duration-200 shadow-sm">
                        <i class="ti ti-check text-base"></i>
                        Simpan Jadwal
                    </button>
                    <button type="submit" name="save_and_add_another" value="1"
                        class="inline-flex items-center gap-2 h-11 px-5 border
                        border-gray-200 text-gray-600 text-sm font-medium rounded-xl
                        hover:bg-gray-50 transition-all duration-200">
                        <i class="ti ti-plus text-base"></i>
                        Simpan & Tambah Lagi
                    </button>
                    <a href="{{ route('admin.showtimes.index') }}"
                        class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Bulk Mode --}}
        <div x-show="mode === 'bulk'" x-data="bulkForm({{ json_encode(
            $cinemas->map(
                    fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'city' => $c->city,
                        'studios' => $c->studios->map(
                                fn($s) => [
                                    'id' => $s->id,
                                    'name' => $s->name,
                                    'type_name' => $s->type_name,
                                    'total_seats' => $s->total_seats,
                                ],
                            )->values()->toArray(),
                    ],
                )->values(),
        ) }},
            {{ json_encode(
                $movies->map(
                    fn($m) => [
                        'id' => $m->id,
                        'title' => $m->title,
                        'duration' => $m->duration,
                        'poster' => $m->poster_url,
                    ],
                ),
            ) }})">

            <form method="POST" action="{{ route('admin.showtimes.bulk') }}" class="space-y-5">
                @csrf

                {{-- Film --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ti ti-movie text-gray-400"></i>
                        Pilih Film
                    </h3>
                    <select name="movie_id" x-model="movieId"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900
                        text-sm rounded-xl px-3 h-11 outline-none cursor-pointer
                        focus:border-gray-400 transition-all duration-200">
                        <option value="">-- Pilih Film --</option>
                        @foreach ($movies as $m)
                            <option value="{{ $m->id }}" data-duration="{{ $m->duration }}">
                                {{ $m->title }} ({{ $m->duration_formatted }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Bioskop & Studio --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ti ti-building text-gray-400"></i>
                        Pilih Bioskop & Studio
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <select @change="onCinemaChange($event.target.value)"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900
                            text-sm rounded-xl px-3 h-11 outline-none cursor-pointer
                            focus:border-gray-400 transition-all duration-200">
                            <option value="">-- Pilih Bioskop --</option>
                            <template x-for="c in cinemas" :key="c.id">
                                <option :value="c.id" x-text="c.name + ' (' + c.city + ')'">
                                </option>
                            </template>
                        </select>
                        <select name="studio_id" :disabled="!studios.length"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900
                            text-sm rounded-xl px-3 h-11 outline-none cursor-pointer
                            focus:border-gray-400 transition-all duration-200
                            disabled:opacity-50">
                            <option value="">-- Pilih Studio --</option>
                            <template x-for="s in studios" :key="s.id">
                                <option :value="s.id" x-text="s.name + ' (' + s.total_seats + ' kursi)'">
                                </option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Tanggal --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ti ti-calendar text-gray-400"></i>
                        Pilih Tanggal
                        <span class="text-xs font-normal text-gray-400">
                            (max 30 tanggal)
                        </span>
                    </h3>

                    {{-- Quick date pickers --}}
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" @click="addDateRange(0, 7)"
                            class="text-xs h-7 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600
                            rounded-lg border border-gray-200 transition">
                            7 hari ke depan
                        </button>
                        <button type="button" @click="addDateRange(0, 14)"
                            class="text-xs h-7 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600
                            rounded-lg border border-gray-200 transition">
                            14 hari ke depan
                        </button>
                        <button type="button" @click="clearDates()"
                            class="text-xs h-7 px-3 bg-red-50 hover:bg-red-100 text-red-600
                            rounded-lg border border-red-200 transition">
                            Hapus semua
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5">
                        @for ($i = 0; $i <= 29; $i++)
                            @php $d = now()->addDays($i); @endphp
                            <label class="cursor-pointer select-none group">
                                <input type="checkbox" name="dates[]" value="{{ $d->format('Y-m-d') }}"
                                    class="sr-only peer">
                                <div
                                    class="p-1.5 text-center border-2 rounded-xl
                            transition-all duration-150
                            peer-checked:border-gray-900 peer-checked:bg-gray-900
                            peer-checked:text-white
                            border-gray-200 hover:border-gray-400 group-hover:bg-gray-50">
                                    <p class="text-[9px] text-inherit opacity-60 leading-none">
                                        {{ $d->format('D') }}
                                    </p>
                                    <p class="text-xs font-bold leading-tight mt-0.5">
                                        {{ $d->format('d') }}
                                    </p>
                                    <p class="text-[9px] text-inherit opacity-60 leading-none">
                                        {{ $d->format('M') }}
                                    </p>
                                </div>
                            </label>
                        @endfor
                    </div>
                </div>

                {{-- Jam tayang --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm" x-data="{ times: ['09:30', '12:30', '15:30', '19:00'] }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="ti ti-clock text-gray-400"></i>
                            Jam Tayang
                        </h3>
                        <button type="button" @click="times.push('')" :disabled="times.length >= 8"
                            class="text-xs h-7 px-3 bg-gray-100 hover:bg-gray-200
                            text-gray-600 rounded-lg border border-gray-200 transition
                            disabled:opacity-40">
                            <i class="ti ti-plus text-xs mr-1"></i>
                            Tambah Jam
                        </button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <template x-for="(t, i) in times" :key="i">
                            <div class="flex items-center gap-2">
                                <input type="time" :name="'times[]'" x-model="times[i]"
                                    class="flex-1 bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400 transition">
                                <button type="button" @click="times.splice(i,1)" x-show="times.length > 1"
                                    class="w-8 h-8 bg-gray-100 hover:bg-red-50
                                    hover:text-red-500 rounded-lg flex items-center
                                    justify-center text-gray-400 transition flex-shrink-0">
                                    <i class="ti ti-x text-sm"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Format, Language, Price --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-semibold text-gray-600 mb-2">
                            Format
                        </label>
                        <select name="format"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900
                            text-sm rounded-xl px-3 h-10 outline-none cursor-pointer
                            focus:border-gray-400 transition">
                            @foreach (['2d' => '2D', '3d' => '3D', 'imax' => 'IMAX', '4dx' => '4DX', 'imax_3d' => 'IMAX 3D', 'dolby' => 'Dolby'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-semibold text-gray-600 mb-2">
                            Bahasa
                        </label>
                        <select name="language"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900
                            text-sm rounded-xl px-3 h-10 outline-none cursor-pointer
                            focus:border-gray-400 transition">
                            @foreach (['sub' => 'Subtitle', 'dub' => 'Dubbing', 'original' => 'Original'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                        <label class="block text-xs font-semibold text-gray-600 mb-2">
                            Harga Regular <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center gap-2 bg-gray-50 border border-gray-200
                        rounded-xl px-3 h-10 focus-within:border-gray-400
                        transition">
                            <span class="text-xs text-gray-400">Rp</span>
                            <input type="number" name="price_regular" value="45000" min="0" step="1000"
                                class="bg-transparent border-none outline-none text-sm
                                text-gray-900 w-full">
                        </div>
                    </div>
                </div>

                {{-- Harga lainnya --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">
                        Harga Tambahan
                        <span class="text-xs font-normal text-gray-400">
                            (kosongkan = sama dg Regular)
                        </span>
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach (['price_student' => 'Pelajar', 'price_senior' => 'Lansia', 'price_vip' => 'VIP/Couple'] as $n => $l)
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                    {{ $l }}
                                </label>
                                <div
                                    class="flex items-center gap-2 bg-gray-50 border border-gray-200
                            rounded-xl px-3 h-10 focus-within:border-gray-400 transition">
                                    <span class="text-xs text-gray-400">Rp</span>
                                    <input type="number" name="{{ $n }}" min="0" step="1000"
                                        placeholder="opsional"
                                        class="bg-transparent border-none outline-none text-sm
                                    text-gray-900 w-full">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                        hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                        transition-all duration-200 shadow-sm">
                        <i class="ti ti-calendar-plus text-base"></i>
                        Buat Jadwal Bulk
                    </button>
                    <a href="{{ route('admin.showtimes.index') }}"
                        class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            function bulkForm(cinemas, movies) {
                return {
                    cinemas,
                    studios: [],
                    movieId: '',

                    onCinemaChange(id) {
                        const c = this.cinemas.find(c => c.id == id);
                        this.studios = c?.studios || [];
                    },

                    addDateRange(from, to) {
                        const checkboxes = document.querySelectorAll('input[name="dates[]"]');
                        checkboxes.forEach((cb, i) => {
                            if (i >= from && i < to) cb.checked = true;
                        });
                    },

                    clearDates() {
                        document.querySelectorAll('input[name="dates[]"]')
                            .forEach(cb => cb.checked = false);
                    }
                }
            }
        </script>
    @endpush

</x-app-layout>
