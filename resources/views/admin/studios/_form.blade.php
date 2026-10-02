{{-- Pilih Bioskop --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100">
        <i class="ti ti-building text-gray-400"></i>
        Pilih Bioskop
    </h3>

    <div x-data="cinemaSelector()"
        x-init="init('{{ old('cinema_id', $studio->cinema_id ?? $selectedCinema?->id ?? '') }}')"
        class="space-y-3">

        {{-- Search bioskop --}}
        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200
            rounded-xl px-3 h-11 focus-within:border-gray-400 focus-within:bg-white
            transition-all duration-200">
            <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
            <input type="text"
                x-model="query"
                @input="filter()"
                placeholder="Cari nama bioskop atau kota..."
                class="bg-transparent border-none outline-none text-sm
                    text-gray-700 placeholder-gray-400 w-full">
            <button x-show="query" @click="query = ''; filter()"
                class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
                <i class="ti ti-x text-xs"></i>
            </button>
        </div>

        {{-- Hidden input --}}
        <input type="hidden" name="cinema_id" :value="selectedId" id="cinema_id">

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72
            overflow-y-auto pr-1">
            <template x-for="cinema in filtered" :key="cinema.id">
                <label class="flex items-center gap-3 p-3 border rounded-xl
                    cursor-pointer transition-all duration-150 hover:border-gray-400"
                    :class="selectedId == cinema.id
                        ? 'border-gray-900 bg-gray-900 shadow-sm'
                        : 'border-gray-200 bg-white'">

                    <input type="radio" name="_cinema_radio" :value="cinema.id"
                        @change="selectedId = cinema.id"
                        :checked="selectedId == cinema.id"
                        class="sr-only">

                    {{-- Radio dot --}}
                    <div class="w-5 h-5 rounded-full border-2 flex items-center
                        justify-center flex-shrink-0 transition-all duration-150"
                        :class="selectedId == cinema.id
                            ? 'border-white bg-white'
                            : 'border-gray-300'">
                        <div class="w-2 h-2 rounded-full bg-gray-900 transition-all"
                            :class="selectedId == cinema.id
                                ? 'opacity-100 scale-100'
                                : 'opacity-0 scale-0'">
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold leading-snug"
                            :class="selectedId == cinema.id
                                ? 'text-white'
                                : 'text-gray-900'"
                            x-text="cinema.name"></p>
                        <p class="text-xs mt-0.5"
                            :class="selectedId == cinema.id
                                ? 'text-gray-300'
                                : 'text-gray-400'"
                            x-text="cinema.city"></p>
                    </div>

                    <i class="ti ti-check text-white text-sm flex-shrink-0
                        transition-all duration-150"
                        :class="selectedId == cinema.id
                            ? 'opacity-100'
                            : 'opacity-0'">
                    </i>

                </label>
            </template>

            <div x-show="filtered.length === 0" class="col-span-2 py-8 text-center">
                <i class="ti ti-building-off text-3xl text-gray-300 block mb-2"></i>
                <p class="text-sm text-gray-400">Bioskop tidak ditemukan</p>
            </div>
        </div>

        {{-- Selected preview --}}
        <div x-show="selectedId" x-transition
            class="flex items-center gap-2 p-3 bg-emerald-50 border
                border-emerald-200 rounded-xl">
            <i class="ti ti-circle-check text-emerald-600 text-base flex-shrink-0"></i>
            <p class="text-xs font-medium text-emerald-700">
                Bioskop dipilih:
                <span class="font-bold" x-text="getSelected()?.name"></span>
            </p>
        </div>

        @error('cinema_id')
        <p class="text-xs text-red-500 flex items-center gap-1">
            <i class="ti ti-alert-circle text-sm"></i>
            {{ $message }}
        </p>
        @enderror
    </div>
</div>

{{-- Info Studio --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100">
        <i class="ti ti-door text-gray-400"></i>
        Informasi Studio
    </h3>

    {{-- Nama + Tipe --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Nama Studio <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name"
                value="{{ old('name', $studio->name ?? '') }}"
                placeholder="cth. Studio 1, IMAX, 4DX Premium..."
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200
                    @error('name') border-red-400 @enderror">
            @error('name')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Tipe Studio <span class="text-red-500">*</span>
            </label>
            <select name="type"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-11 outline-none cursor-pointer
                    focus:border-gray-400 transition-all duration-200">
                @foreach([
                    'regular'  => ['Regular',  'bg-gray-100',   'text-gray-600'],
                    '3d'       => ['3D',        'bg-cyan-50',    'text-cyan-600'],
                    'imax'     => ['IMAX',      'bg-blue-50',    'text-blue-600'],
                    '4dx'      => ['4DX',       'bg-purple-50',  'text-purple-600'],
                    'vip'      => ['VIP',       'bg-amber-50',   'text-amber-600'],
                    'premiere' => ['Premiere',  'bg-rose-50',    'text-rose-600'],
                ] as $val => [$label, $bg, $text])
                <option value="{{ $val }}"
                    {{ old('type', $studio->type ?? 'regular') === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            @error('type')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Fasilitas --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">
            Fasilitas Studio
        </label>
        <div class="flex flex-wrap gap-2">
            @foreach([
                'dolby_stereo'   => 'Dolby Stereo',
                'dolby_atmos'    => 'Dolby Atmos',
                '4dx_motion'     => '4DX Motion',
                'water_effect'   => 'Water Effect',
                'wind_effect'    => 'Wind Effect',
                'scent'          => 'Scent',
                'imax_laser'     => 'IMAX Laser',
                '4k_projection'  => '4K Projection',
                '3d_projection'  => '3D Projection',
                'recliner'       => 'Recliner',
                'food_service'   => 'Food Service',
                'private_lounge' => 'Private Lounge',
            ] as $val => $label)
            @php
                $checked = in_array($val,
                    old('facilities', $studio->facilities ?? [])
                );
            @endphp
            <label class="flex items-center gap-1.5 text-xs font-medium px-3
                py-1.5 rounded-xl border cursor-pointer transition-all duration-150
                {{ $checked
                    ? 'border-gray-900 bg-gray-900 text-white'
                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400' }}">
                <input type="checkbox" name="facilities[]"
                    value="{{ $val }}"
                    {{ $checked ? 'checked' : '' }}
                    class="sr-only"
                    onchange="this.closest('label').classList.toggle('border-gray-900');
                              this.closest('label').classList.toggle('bg-gray-900');
                              this.closest('label').classList.toggle('text-white');
                              this.closest('label').classList.toggle('border-gray-200');
                              this.closest('label').classList.toggle('bg-white');
                              this.closest('label').classList.toggle('text-gray-600')">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Layout Kursi --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4"
    x-data="layoutPreview()"
    x-init="init(
        {{ old('rows', $studio->rows ?? 8) }},
        {{ old('cols', $studio->cols ?? 16) }},
        '{{ old('vip_rows', '') }}',
        '{{ old('couple_rows', '') }}',
        '{{ old('aisle_after', '') }}'
    )">

    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <i class="ti ti-layout-grid text-gray-400"></i>
            Layout Kursi
        </h3>
        @if(isset($studio) && $studio->exists)
        <span class="text-xs text-amber-600 bg-amber-50 border border-amber-200
            px-2.5 py-1 rounded-xl flex items-center gap-1.5">
            <i class="ti ti-alert-triangle text-xs"></i>
            Edit tidak mengubah layout kursi
        </span>
        @endif
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Jumlah Baris <span class="text-red-500">*</span>
            </label>
            <input type="number" name="rows" min="1" max="26"
                x-model.number="rows"
                @input.debounce.300ms="renderPreview()"
                {{ isset($studio) && $studio->exists ? 'disabled' : '' }}
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <p class="text-[10px] text-gray-400 mt-1">A–Z (maks. 26)</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Jumlah Kolom <span class="text-red-500">*</span>
            </label>
            <input type="number" name="cols" min="1" max="40"
                x-model.number="cols"
                @input.debounce.300ms="renderPreview()"
                {{ isset($studio) && $studio->exists ? 'disabled' : '' }}
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <p class="text-[10px] text-gray-400 mt-1">Maks. 40</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Baris VIP
            </label>
            <input type="text" name="vip_rows"
                x-model="vipRows"
                @input.debounce.400ms="renderPreview()"
                placeholder="cth. A,B"
                {{ isset($studio) && $studio->exists ? 'disabled' : '' }}
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            <p class="text-[10px] text-gray-400 mt-1">Pisah dengan koma</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Baris Couple
            </label>
            <input type="text" name="couple_rows"
                x-model="coupleRows"
                @input.debounce.400ms="renderPreview()"
                placeholder="cth. H,I"
                {{ isset($studio) && $studio->exists ? 'disabled' : '' }}
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Gang (Aisle) Setelah Kolom ke-
            </label>
            <input type="text" name="aisle_after"
                x-model="aisleAfter"
                @input.debounce.400ms="renderPreview()"
                placeholder="cth. 4,12 (gang setelah kolom 4 dan 12)"
                {{ isset($studio) && $studio->exists ? 'disabled' : '' }}
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
        </div>
    </div>

    {{-- Live preview --}}
    <div class="border border-gray-200 rounded-xl bg-gray-50 p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-600">
                Preview Layout
            </p>
            <div class="flex items-center gap-3 text-[10px] text-gray-500">
                <span class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-emerald-200 border border-emerald-300 rounded-[2px]"></span>
                    Regular
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-violet-200 border border-violet-300 rounded-[2px]"></span>
                    VIP
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-pink-200 border border-pink-300 rounded-[2px]"></span>
                    Couple
                </span>
            </div>
        </div>

        {{-- Screen --}}
        <div class="text-center mb-3">
            <div class="h-1.5 bg-gradient-to-r from-transparent via-gray-400
                to-transparent rounded-full mx-8"></div>
            <p class="text-[9px] text-gray-400 tracking-widest mt-1 uppercase">
                Layar
            </p>
        </div>

        {{-- Grid --}}
        <div class="overflow-x-auto">
            <div class="space-y-1 min-w-max mx-auto" id="seat-preview">
                <template x-for="(row, ri) in previewRows" :key="ri">
                    <div class="flex items-center gap-1">
                        <span class="text-[9px] text-gray-400 w-4 text-right flex-shrink-0"
                            x-text="row.label"></span>
                        <div class="flex gap-0.5">
                            <template x-for="(seat, ci) in row.seats" :key="ci">
                                <div>
                                    <div x-show="seat.aisle" class="w-3 h-5"></div>
                                    <div x-show="!seat.aisle"
                                        class="w-5 h-5 rounded-[2px] border transition-colors"
                                        :class="{
                                            'bg-emerald-100 border-emerald-300': seat.type === 'regular',
                                            'bg-violet-100 border-violet-300':  seat.type === 'vip',
                                            'bg-pink-100 border-pink-300':      seat.type === 'couple',
                                        }">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Summary --}}
        <div class="mt-3 pt-3 border-t border-gray-200 flex items-center
            gap-4 text-xs text-gray-500 flex-wrap">
            <span>
                Total:
                <strong class="text-gray-700" x-text="totalSeats + ' kursi'"></strong>
            </span>
            <span x-show="vipCount > 0">
                VIP:
                <strong class="text-violet-600" x-text="vipCount"></strong>
            </span>
            <span x-show="coupleCount > 0">
                Couple:
                <strong class="text-pink-600" x-text="coupleCount"></strong>
            </span>
        </div>
    </div>
</div>

{{-- Pengaturan --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2
        pb-3 border-b border-gray-100 mb-4">
        <i class="ti ti-settings text-gray-400"></i>
        Pengaturan
    </h3>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Urutan Tampil
            </label>
            <input type="number" name="order" min="0"
                value="{{ old('order', $studio->order ?? 0) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900
                    text-sm rounded-xl px-3 h-10 outline-none focus:border-gray-400
                    transition-all duration-200">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Status
            </label>
            <div class="flex items-center gap-3 h-10"
                x-data="{ on: {{ old('is_active', $studio->is_active ?? true) ? 'true' : 'false' }} }">
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-2 cursor-pointer">
                    <div @click="on = !on"
                        :class="on ? 'bg-gray-900' : 'bg-gray-200'"
                        class="w-11 h-6 rounded-full relative cursor-pointer
                            transition-colors duration-200">
                        <div :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                            class="absolute top-0.5 w-5 h-5 bg-white rounded-full
                                shadow-sm transition-transform duration-200">
                        </div>
                        <input type="checkbox" name="is_active" value="1"
                            x-model="on" class="sr-only">
                    </div>
                    <span class="text-sm font-medium text-gray-700">Aktif</span>
                </label>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Cinema selector
const ALL_CINEMAS = @json($cinemas);

function cinemaSelector() {
    return {
        query:      '',
        selectedId: '',
        filtered:   ALL_CINEMAS,

        init(preselect) {
            if (preselect) this.selectedId = String(preselect);
        },

        filter() {
            const q = this.query.toLowerCase();
            this.filtered = q
                ? ALL_CINEMAS.filter(c =>
                    c.name.toLowerCase().includes(q) ||
                    c.city.toLowerCase().includes(q))
                : ALL_CINEMAS;
        },

        getSelected() {
            return ALL_CINEMAS.find(c => String(c.id) === String(this.selectedId));
        }
    }
}

// Layout preview
function layoutPreview() {
    return {
        rows:        8,
        cols:        16,
        vipRows:     '',
        coupleRows:  '',
        aisleAfter:  '',
        previewRows: [],
        totalSeats:  0,
        vipCount:    0,
        coupleCount: 0,

        init(rows, cols, vip, couple, aisle) {
            this.rows       = rows || 8;
            this.cols       = cols || 16;
            this.vipRows    = vip    || '';
            this.coupleRows = couple || '';
            this.aisleAfter = aisle  || '';
            this.renderPreview();
        },

        parseList(str, mode = 'str') {
            if (!str || !str.trim()) return [];
            return str.split(',')
                .map(s => s.trim())
                .filter(Boolean)
                .map(s => mode === 'int' ? parseInt(s) : s.toUpperCase());
        },

        renderPreview() {
            const rows      = Math.min(Math.max(parseInt(this.rows) || 1, 1), 26);
            const cols      = Math.min(Math.max(parseInt(this.cols) || 1, 1), 40);
            const vipRows   = this.parseList(this.vipRows);
            const couple    = this.parseList(this.coupleRows);
            const aisles    = this.parseList(this.aisleAfter, 'int');

            this.previewRows = [];
            this.totalSeats  = 0;
            this.vipCount    = 0;
            this.coupleCount = 0;

            for (let r = 0; r < rows; r++) {
                const label  = String.fromCharCode(65 + r);
                const seats  = [];

                for (let c = 1; c <= cols; c++) {
                    if (aisles.includes(c)) {
                        seats.push({ aisle: true });
                        continue;
                    }
                    let type = 'regular';
                    if (vipRows.includes(label))  type = 'vip';
                    if (couple.includes(label))   type = 'couple';

                    seats.push({ aisle: false, type });
                    this.totalSeats++;
                    if (type === 'vip')    this.vipCount++;
                    if (type === 'couple') this.coupleCount++;
                }

                this.previewRows.push({ label, seats });
            }
        }
    }
}
</script>
@endpush