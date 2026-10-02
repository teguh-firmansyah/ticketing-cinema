{{-- Cinema & Studio data untuk Alpine --}}
@php
    $cinemasJson = $cinemas
        ->map(
            fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'city' => $c->city,
                'studios' => $c->studios
                    ->map(
                        fn($s) => [
                            'id' => $s->id,
                            'name' => $s->name,
                            'type' => $s->type,
                            'type_name' => $s->type_name,
                            'total_seats' => $s->total_seats,
                        ],
                    )
                    ->values()
                    ->toArray(),
            ],
        )
        ->values()
        ->toJson();
@endphp

<div x-data="showtimeForm({{ $cinemasJson }})" x-init="init(
    '{{ old('cinema_id', $showtime->studio->cinema_id ?? ($selectedCinema?->id ?? '')) }}',
    '{{ old('studio_id', $showtime->studio_id ?? '') }}',
    '{{ old('movie_id', $showtime->movie_id ?? ($selectedMovie?->id ?? '')) }}'
)" class="space-y-5">

    {{-- Pilih Film --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <h3
            class="text-sm font-semibold text-gray-700 flex items-center gap-2
            pb-3 border-b border-gray-100 mb-4">
            <i class="ti ti-movie text-gray-400"></i>
            Pilih Film
        </h3>

        <div class="relative">
            <div
                class="flex items-center gap-2 bg-gray-50 border border-gray-200
                rounded-xl px-3 h-11 mb-3 focus-within:border-gray-400
                focus-within:bg-white transition-all duration-200">
                <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                <input type="text" x-model="movieQuery" @input="filterMovies()" placeholder="Cari judul film..."
                    class="bg-transparent border-none outline-none text-sm
                        text-gray-700 placeholder-gray-400 w-full">
            </div>

            <input type="hidden" name="movie_id" :value="selectedMovieId">

            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2
                max-h-56 overflow-y-auto">
                <template x-for="m in filteredMovies" :key="m.id">
                    <div @click="selectMovie(m)"
                        class="cursor-pointer rounded-xl overflow-hidden border-2
                            transition-all duration-150"
                        :class="selectedMovieId == m.id ?
                            'border-gray-900 ring-2 ring-gray-900 ring-offset-1' :
                            'border-transparent hover:border-gray-300'">
                        <div class="aspect-[2/3] bg-gray-100 overflow-hidden relative">
                            <img :src="m.poster" :alt="m.title" loading="lazy"
                                class="w-full h-full object-cover">
                            <div x-show="selectedMovieId == m.id"
                                class="absolute inset-0 bg-gray-900/40 flex items-center
                                    justify-center">
                                <i class="ti ti-check text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="p-1.5 bg-white">
                            <p class="text-[10px] font-semibold text-gray-900
                                line-clamp-2 leading-tight"
                                x-text="m.title"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Selected movie info --}}
            <div x-show="selectedMovie" x-transition
                class="mt-3 flex items-center gap-3 p-3 bg-gray-50
                    border border-gray-200 rounded-xl">
                <i class="ti ti-circle-check text-emerald-500 flex-shrink-0"></i>
                <div>
                    <p class="text-xs font-bold text-gray-900" x-text="selectedMovie?.title"></p>
                    <p class="text-[10px] text-gray-500"
                        x-text="selectedMovie?.duration
                            ? 'Durasi: ' + selectedMovie.duration + ' menit'
                            : ''">
                    </p>
                </div>
            </div>
        </div>

        @error('movie_id')
            <p class="text-xs text-red-500 mt-2 flex items-center gap-1">
                <i class="ti ti-alert-circle text-sm"></i>{{ $message }}
            </p>
        @enderror
    </div>

    {{-- Pilih Bioskop & Studio --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <h3
            class="text-sm font-semibold text-gray-700 flex items-center gap-2
            pb-3 border-b border-gray-100 mb-4">
            <i class="ti ti-building text-gray-400"></i>
            Pilih Bioskop & Studio
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            {{-- Cinema --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Bioskop <span class="text-red-500">*</span>
                </label>
                <select @change="onCinemaChange($event.target.value)"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900
                        text-sm rounded-xl px-3 h-11 outline-none cursor-pointer
                        focus:border-gray-400 transition-all duration-200">
                    <option value="">-- Pilih Bioskop --</option>
                    <template x-for="c in cinemas" :key="c.id">
                        <option :value="c.id" :selected="selectedCinemaId == c.id"
                            x-text="c.name + ' (' + c.city + ')'">
                        </option>
                    </template>
                </select>
            </div>

            {{-- Studio --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Studio <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="studio_id" :value="selectedStudioId">
                <select @change="onStudioChange($event.target.value)"
                    :disabled="!selectedCinemaId || availableStudios.length === 0"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900
                        text-sm rounded-xl px-3 h-11 outline-none cursor-pointer
                        focus:border-gray-400 transition-all duration-200
                        disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">-- Pilih Studio --</option>
                    <template x-for="s in availableStudios" :key="s.id">
                        <option :value="s.id" :selected="selectedStudioId == s.id"
                            x-text="s.name + ' (' + s.type_name + ', ' + s.total_seats + ' kursi)'">
                        </option>
                    </template>
                </select>
            </div>
        </div>

        {{-- Selected studio info --}}
        <div x-show="selectedStudio" x-transition
            class="flex items-center gap-4 p-3 bg-gray-50 border border-gray-200
                rounded-xl">
            @php
                $typeColors = [
                    'imax' => 'bg-blue-50 border-blue-100 text-blue-700',
                    '4dx' => 'bg-purple-50 border-purple-100 text-purple-700',
                    '3d' => 'bg-cyan-50 border-cyan-100 text-cyan-700',
                    'vip' => 'bg-amber-50 border-amber-100 text-amber-700',
                    'premiere' => 'bg-rose-50 border-rose-100 text-rose-700',
                    'regular' => 'bg-gray-100 border-gray-200 text-gray-600',
                ];
            @endphp
            <div class="flex items-center gap-2 flex-1">
                <div
                    class="w-9 h-9 bg-gray-200 rounded-xl flex items-center
                    justify-center flex-shrink-0">
                    <i class="ti ti-door text-gray-600 text-base"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-900" x-text="selectedStudio?.name"></p>
                    <p class="text-[10px] text-gray-500" x-text="(selectedStudio?.total_seats || 0) + ' kursi'">
                    </p>
                </div>
            </div>
        </div>

        @error('studio_id')
            <p class="text-xs text-red-500 mt-2 flex items-center gap-1">
                <i class="ti ti-alert-circle text-sm"></i>{{ $message }}
            </p>
        @enderror
    </div>

    {{-- Waktu Tayang --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <h3
            class="text-sm font-semibold text-gray-700 flex items-center gap-2
            pb-3 border-b border-gray-100 mb-4">
            <i class="ti ti-clock text-gray-400"></i>
            Waktu Tayang
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Mulai <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="start_time" x-model="startTime"
                    @change="calcEndTime(); checkConflict()"
                    value="{{ old('start_time', isset($showtime->start_time) ? $showtime->start_time->format('Y-m-d\TH:i') : '') }}"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900
                        text-sm rounded-xl px-3 h-11 outline-none focus:border-gray-400
                        transition-all duration-200
                        @error('start_time') border-red-400 @enderror">
                @error('start_time')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    Selesai <span class="text-red-500">*</span>
                    <span class="text-gray-400 font-normal">
                        (auto dari durasi film)
                    </span>
                </label>
                <input type="datetime-local" name="end_time" x-model="endTime" @change="checkConflict()"
                    value="{{ old('end_time', isset($showtime->end_time) ? $showtime->end_time->format('Y-m-d\TH:i') : '') }}"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900
                        text-sm rounded-xl px-3 h-11 outline-none focus:border-gray-400
                        transition-all duration-200">
            </div>
        </div>

        {{-- Conflict warning --}}
        <div x-show="conflictMsg" x-transition
            class="mt-3 flex items-start gap-2 p-3 bg-red-50 border
                border-red-200 rounded-xl">
            <i class="ti ti-alert-triangle text-red-500 text-sm flex-shrink-0 mt-0.5"></i>
            <p class="text-xs text-red-600" x-text="conflictMsg"></p>
        </div>

        {{-- No conflict --}}
        <div x-show="conflictChecked && !conflictMsg && startTime && endTime" x-transition
            class="mt-3 flex items-center gap-2 p-3 bg-emerald-50 border
                border-emerald-200 rounded-xl">
            <i class="ti ti-circle-check text-emerald-500 text-sm flex-shrink-0"></i>
            <p class="text-xs text-emerald-600">Waktu tersedia, tidak ada konflik.</p>
        </div>

        @error('end_time')
            <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Format & Bahasa --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <h3
            class="text-sm font-semibold text-gray-700 flex items-center gap-2
            pb-3 border-b border-gray-100 mb-4">
            <i class="ti ti-3d-cube-sphere text-gray-400"></i>
            Format & Bahasa
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Format --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                    Format <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
        '2d' => ['2D', 'bg-gray-100 border-gray-300 text-gray-700'],
        '3d' => ['3D', 'bg-cyan-50 border-cyan-300 text-cyan-700'],
        'imax' => ['IMAX', 'bg-blue-50 border-blue-300 text-blue-700'],
        '4dx' => ['4DX', 'bg-purple-50 border-purple-300 text-purple-700'],
        'imax_3d' => ['IMAX 3D', 'bg-indigo-50 border-indigo-300 text-indigo-700'],
        'dolby' => ['Dolby', 'bg-rose-50 border-rose-300 text-rose-700'],
    ] as $val => [$label, $cls])
                        @php $isActive = old('format', $showtime->format ?? '2d') === $val; @endphp
                        <label
                            class="flex items-center justify-center h-10 border-2
                        rounded-xl cursor-pointer font-bold text-xs
                        transition-all duration-150 hover:opacity-90
                        {{ $isActive ? $cls . ' ring-2 ring-offset-1 ring-gray-400' : 'border-gray-200 bg-white text-gray-500' }}">
                            <input type="radio" name="format" value="{{ $val }}"
                                {{ $isActive ? 'checked' : '' }} class="sr-only" onchange="highlightFormat(this)">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Bahasa --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                    Bahasa <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
        'sub' => 'Subtitel',
        'dub' => 'Dubbing',
        'original' => 'Original',
    ] as $val => $label)
                        @php $isActive = old('language', $showtime->language ?? 'sub') === $val; @endphp
                        <label
                            class="flex items-center justify-center h-10 border-2
                        rounded-xl cursor-pointer font-semibold text-xs
                        transition-all duration-150
                        {{ $isActive
                            ? 'border-gray-900 bg-gray-900 text-white'
                            : 'border-gray-200 bg-white text-gray-500 hover:border-gray-400' }}">
                            <input type="radio" name="language" value="{{ $val }}"
                                {{ $isActive ? 'checked' : '' }} class="sr-only" onchange="highlightLanguage(this)">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Harga --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <h3
            class="text-sm font-semibold text-gray-700 flex items-center gap-2
            pb-3 border-b border-gray-100 mb-4">
            <i class="ti ti-currency-dollar text-gray-400"></i>
            Harga Tiket
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ([['name' => 'price_regular', 'label' => 'Regular', 'required' => true, 'hint' => 'Harga utama'], ['name' => 'price_student', 'label' => 'Pelajar/Mahasiswa', 'required' => false, 'hint' => 'Kosongkan = sama dg regular'], ['name' => 'price_senior', 'label' => 'Lansia (60+)', 'required' => false, 'hint' => 'Kosongkan = sama dg regular'], ['name' => 'price_vip', 'label' => 'VIP / Couple', 'required' => false, 'hint' => 'Kosongkan = sama dg regular']] as $p)
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        {{ $p['label'] }}
                        @if ($p['required'])
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <div
                        class="flex items-center gap-2 bg-gray-50 border border-gray-200
                    rounded-xl px-3 h-10 focus-within:border-gray-400
                    focus-within:bg-white transition-all duration-200">
                        <span class="text-xs text-gray-400 font-medium flex-shrink-0">
                            Rp
                        </span>
                        <input type="number" name="{{ $p['name'] }}"
                            value="{{ old($p['name'], $showtime->{$p['name']} ?? '') }}" min="0"
                            step="1000" placeholder="{{ $p['required'] ? '45000' : 'opsional' }}"
                            class="bg-transparent border-none outline-none text-sm
                            text-gray-900 w-full">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">{{ $p['hint'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Quick price presets --}}
        <div class="mt-4 flex flex-wrap gap-2">
            <p class="text-xs text-gray-500 w-full">Preset harga cepat:</p>
            @foreach ([['label' => 'Regular 2D', 'regular' => 45000, 'vip' => 75000], ['label' => '3D', 'regular' => 55000, 'vip' => 85000], ['label' => 'IMAX', 'regular' => 85000, 'vip' => 120000], ['label' => '4DX', 'regular' => 100000, 'vip' => 130000], ['label' => 'VIP Studio', 'regular' => 0, 'vip' => 150000]] as $preset)
                <button type="button" onclick="applyPricePreset({{ $preset['regular'] }}, {{ $preset['vip'] }})"
                    class="text-xs px-3 h-7 bg-gray-100 hover:bg-gray-200 text-gray-600
                    rounded-lg border border-gray-200 transition-all duration-150">
                    {{ $preset['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Catatan --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            Catatan (opsional)
        </label>
        <textarea name="notes" rows="2" placeholder="cth. Special screening, screening dengan Q&A, dll."
            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                rounded-xl px-4 py-3 outline-none focus:border-gray-400 focus:bg-white
                transition-all duration-200 resize-none">{{ old('notes', $showtime->notes ?? '') }}</textarea>
    </div>

</div>

@php
    $allMovies = $movies->map(
        fn($m) => [
            'id' => $m->id,
            'title' => $m->title,
            'duration' => $m->duration,
            'poster' => $m->poster_url,
            'status' => $m->status,
        ],
    );
@endphp

@push('scripts')
    <script>
        const ALL_MOVIES = @json($allMovies);

        function showtimeForm(cinemas) {
            return {
                cinemas,
                movieQuery: '',
                filteredMovies: ALL_MOVIES,
                selectedMovieId: null,
                selectedMovie: null,
                selectedCinemaId: null,
                selectedStudioId: null,
                selectedStudio: null,
                availableStudios: [],
                startTime: '',
                endTime: '',
                conflictMsg: '',
                conflictChecked: false,
                conflictTimer: null,

                init(preselCinema, preselStudio, preselMovie) {
                    if (preselMovie) {
                        const m = ALL_MOVIES.find(m => m.id == preselMovie);
                        if (m) this.selectMovie(m);
                    }
                    if (preselCinema) {
                        this.onCinemaChange(preselCinema);
                        if (preselStudio) {
                            this.$nextTick(() => this.onStudioChange(preselStudio));
                        }
                    }
                },

                filterMovies() {
                    const q = this.movieQuery.toLowerCase();
                    this.filteredMovies = q ?
                        ALL_MOVIES.filter(m => m.title.toLowerCase().includes(q)) :
                        ALL_MOVIES;
                },

                selectMovie(m) {
                    this.selectedMovieId = m.id;
                    this.selectedMovie = m;
                    this.movieQuery = '';
                    this.filteredMovies = ALL_MOVIES;
                    if (this.startTime) this.calcEndTime();
                },

                onCinemaChange(cinemaId) {
                    this.selectedCinemaId = cinemaId;
                    this.selectedStudioId = null;
                    this.selectedStudio = null;
                    const cinema = this.cinemas.find(c => c.id == cinemaId);
                    this.availableStudios = cinema?.studios || [];
                },

                onStudioChange(studioId) {
                    this.selectedStudioId = studioId;
                    this.selectedStudio = this.availableStudios.find(s => s.id == studioId);
                    if (this.startTime) this.checkConflict();
                },

                calcEndTime() {
                    if (!this.startTime || !this.selectedMovie?.duration) return;
                    const start = new Date(this.startTime);
                    const end = new Date(
                        start.getTime() + (this.selectedMovie.duration + 30) * 60000
                    );
                    this.endTime = end.toISOString().slice(0, 16);
                },

                async checkConflict() {
                    if (!this.selectedStudioId || !this.startTime || !this.endTime) return;

                    clearTimeout(this.conflictTimer);
                    this.conflictTimer = setTimeout(async () => {
                        try {
                            const res = await fetch(
                                '{{ route('admin.showtimes.ajax.conflict') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        studio_id: this.selectedStudioId,
                                        start_time: this.startTime,
                                        end_time: this.endTime,
                                        showtime_id: {{ $showtime->id ?? 'null' }},
                                    }),
                                }
                            );
                            const data = await res.json();
                            this.conflictMsg = data.conflict ? data.message : '';
                            this.conflictChecked = true;
                        } catch (e) {
                            console.error(e);
                        }
                    }, 600);
                }
            }
        }

        // Format radio
        function highlightFormat(el) {
            document.querySelectorAll('[name="format"]').forEach(r => {
                const label = r.closest('label');
                label.classList.remove('ring-2', 'ring-offset-1', 'ring-gray-400');
                label.className = label.className
                    .replace(/bg-\S+|border-\S+|text-\S+/g, '')
                    .trim() + ' border-gray-200 bg-white text-gray-500';
            });
            const label = el.closest('label');
            const colors = {
                '2d': 'border-gray-300 bg-gray-100 text-gray-700',
                '3d': 'border-cyan-300 bg-cyan-50 text-cyan-700',
                'imax': 'border-blue-300 bg-blue-50 text-blue-700',
                '4dx': 'border-purple-300 bg-purple-50 text-purple-700',
                'imax_3d': 'border-indigo-300 bg-indigo-50 text-indigo-700',
                'dolby': 'border-rose-300 bg-rose-50 text-rose-700',
            };
            label.className = label.className
                .replace(/border-gray-200|bg-white|text-gray-500/g, '')
                .trim() + ' ' + (colors[el.value] || '') + ' ring-2 ring-offset-1 ring-gray-400';
        }

        // Language radio
        function highlightLanguage(el) {
            document.querySelectorAll('[name="language"]').forEach(r => {
                const lbl = r.closest('label');
                lbl.className = lbl.className
                    .replace(/border-gray-900|bg-gray-900|text-white/g, '')
                    .trim() + ' border-gray-200 bg-white text-gray-500';
            });
            const lbl = el.closest('label');
            lbl.className = lbl.className
                .replace(/border-gray-200|bg-white|text-gray-500/g, '')
                .trim() + ' border-gray-900 bg-gray-900 text-white';
        }

        // Price preset
        function applyPricePreset(regular, vip) {
            const fields = {
                price_regular: regular,
                price_student: Math.round(regular * 0.8),
                price_senior: Math.round(regular * 0.75),
                price_vip: vip,
            };
            Object.entries(fields).forEach(([name, val]) => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.value = val || '';
            });
        }
    </script>
@endpush
