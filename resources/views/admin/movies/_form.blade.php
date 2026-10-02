{{-- Info Dasar --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100">
        <i class="ti ti-movie text-gray-400"></i>
        Informasi Film
    </h3>

    {{-- Judul --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Judul Film <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title', $movie->title ?? '') }}" placeholder="Judul film"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200
                    @error('title') border-red-400 @enderror">
            @error('title')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Judul Asli
            </label>
            <input type="text" name="original_title"
                value="{{ old('original_title', $movie->original_title ?? '') }}" placeholder="Original title"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
    </div>

    {{-- Synopsis --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sinopsis</label>
        <textarea name="synopsis" rows="4" placeholder="Sinopsis film..."
            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                rounded-xl px-4 py-3 outline-none focus:border-gray-400 focus:bg-white
                transition-all duration-200 resize-none">{{ old('synopsis', $movie->synopsis ?? '') }}</textarea>
    </div>

    {{-- Genre --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Genre</label>
        <div class="flex flex-wrap gap-2">
            @foreach (['Action', 'Adventure', 'Animation', 'Comedy', 'Crime', 'Drama', 'Family', 'Fantasy', 'History', 'Horror', 'Music', 'Mystery', 'Romance', 'Sci-Fi', 'Thriller', 'War'] as $g)
                @php $checked = in_array($g, old('genres', $movie->genres ?? [])); @endphp
                <label
                    class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5
                rounded-xl border cursor-pointer transition-all duration-150
                {{ $checked
                    ? 'border-gray-900 bg-gray-900 text-white'
                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400' }}">
                    <input type="checkbox" name="genres[]" value="{{ $g }}" {{ $checked ? 'checked' : '' }}
                        class="sr-only" onchange="toggleChip(this)">
                    {{ $g }}
                </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Media --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100">
        <i class="ti ti-photo text-gray-400"></i>
        Media
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Poster --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Poster (URL atau Upload)
            </label>
            <input type="text" name="poster" value="{{ old('poster', $movie->poster ?? '') }}"
                placeholder="https://... atau kosongkan untuk upload"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-10 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200 mb-2">
            <input type="file" name="poster_upload" accept="image/*"
                class="text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3
                    file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700
                    file:text-xs file:cursor-pointer hover:file:bg-gray-200">
            <p class="text-[10px] text-gray-400 mt-1">
                URL TMDb atau upload file (JPG, PNG, WebP, maks 3MB)
            </p>
        </div>

        {{-- Backdrop --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Backdrop URL
            </label>
            <input type="text" name="backdrop" value="{{ old('backdrop', $movie->backdrop ?? '') }}"
                placeholder="https://image.tmdb.org/..."
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-10 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
    </div>

    {{-- Trailer --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            URL Trailer YouTube
        </label>
        <div
            class="flex items-center gap-2 bg-gray-50 border border-gray-200
            rounded-xl px-3 h-10 focus-within:border-gray-400 focus-within:bg-white
            transition-all duration-200">
            <i class="ti ti-brand-youtube text-red-500 text-sm flex-shrink-0"></i>
            <input type="url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url ?? '') }}"
                placeholder="https://www.youtube.com/watch?v=..."
                class="bg-transparent border-none outline-none text-sm text-gray-900
                    placeholder-gray-400 w-full">
        </div>
    </div>
</div>

{{-- Detail --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100">
        <i class="ti ti-info-circle text-gray-400"></i>
        Detail Film
    </h3>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Durasi (menit)
            </label>
            <input type="number" name="duration" min="1" max="600"
                value="{{ old('duration', $movie->duration ?? '') }}" placeholder="120"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Rating Usia <span class="text-red-500">*</span>
            </label>
            <select name="age_rating"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none cursor-pointer
                    focus:border-gray-400 transition-all duration-200">
                @foreach (['SU' => 'SU (Semua Umur)', '13+' => '13+', '17+' => '17+', '21+' => '21+'] as $val => $label)
                    <option value="{{ $val }}"
                        {{ old('age_rating', $movie->age_rating ?? 'SU') === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Bahasa
            </label>
            <select name="language"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none cursor-pointer
                    focus:border-gray-400 transition-all duration-200">
                <option value="id" {{ old('language', $movie->language ?? 'id') === 'id' ? 'selected' : '' }}>
                    Indonesia</option>
                <option value="en" {{ old('language', $movie->language ?? '') === 'en' ? 'selected' : '' }}>English
                </option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Tanggal Rilis
            </label>
            <input type="date" name="release_date"
                value="{{ old('release_date', isset($movie) ? $movie->release_date?->format('Y-m-d') : '') }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Sutradara
            </label>
            <input type="text" name="director" value="{{ old('director', $movie->director ?? '') }}"
                placeholder="Nama sutradara"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-4 h-10 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Rating TMDb
            </label>
            <input type="number" name="vote_average" min="0" max="10" step="0.1"
                value="{{ old('vote_average', $movie->vote_average ?? '') }}" placeholder="7.5"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200">
        </div>
    </div>

    {{-- Cast --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            Pemeran
            <span class="text-gray-400 font-normal">(satu nama per baris)</span>
        </label>
        <textarea name="cast" rows="3" placeholder="Chris Pratt&#10;Samuel L. Jackson&#10;..."
            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                rounded-xl px-4 py-3 outline-none focus:border-gray-400 focus:bg-white
                transition-all duration-200 resize-none">{{ old('cast', isset($movie) ? implode("\n", $movie->cast ?? []) : '') }}</textarea>
    </div>
</div>

{{-- Pengaturan --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100">
        <i class="ti ti-settings text-gray-400"></i>
        Pengaturan
    </h3>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Status <span class="text-red-500">*</span>
            </label>
            <select name="status"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none cursor-pointer
                    focus:border-gray-400 transition-all duration-200">
                <option value="coming_soon"
                    {{ old('status', $movie->status ?? '') === 'coming_soon' ? 'selected' : '' }}>Segera Hadir</option>
                <option value="now_showing"
                    {{ old('status', $movie->status ?? '') === 'now_showing' ? 'selected' : '' }}>Sedang Tayang
                </option>
                <option value="ended" {{ old('status', $movie->status ?? '') === 'ended' ? 'selected' : '' }}>
                    Selesai</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                TMDb ID
            </label>
            <input type="number" name="tmdb_id" value="{{ old('tmdb_id', $movie->tmdb_id ?? '') }}"
                placeholder="opsional"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200">
        </div>
        <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <div class="relative" x-data="{ on: {{ old('is_featured', $movie->is_featured ?? false) ? 'true' : 'false' }} }">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" x-model="on"
                        {{ old('is_featured', $movie->is_featured ?? false) ? 'checked' : '' }} class="sr-only">
                    <div @click="on = !on" :class="on ? 'bg-gray-900' : 'bg-gray-200'"
                        class="w-11 h-6 rounded-full relative cursor-pointer
                            transition-colors duration-200">
                        <div :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                            class="absolute top-0.5 w-5 h-5 bg-white rounded-full
                                shadow-sm transition-transform duration-200">
                        </div>
                    </div>
                </div>
                <span class="text-sm font-medium text-gray-700">Featured</span>
            </label>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function toggleChip(el) {
            const label = el.closest('label');
            const on = el.checked;
            label.classList.toggle('border-gray-900', on);
            label.classList.toggle('bg-gray-900', on);
            label.classList.toggle('text-white', on);
            label.classList.toggle('border-gray-200', !on);
            label.classList.toggle('bg-white', !on);
            label.classList.toggle('text-gray-600', !on);
        }
    </script>
@endpush
