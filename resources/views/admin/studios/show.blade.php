<x-app-layout>
    <x-slot name="header">Detail Studio</x-slot>

    <div class="p-6 space-y-6" x-data="{ regenOpen: false }">

        {{-- Flash --}}
        @foreach (['success' => 'emerald', 'error' => 'red'] as $type => $color)
            @if (session($type))
                <div class="flex items-center gap-3 bg-{{ $color }}-50 border
        border-{{ $color }}-200 text-{{ $color }}-700 text-sm px-4 py-3 rounded-xl"
                    x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    <i
                        class="ti ti-{{ $type === 'success' ? 'circle-check' : 'alert-circle' }}
            flex-shrink-0"></i>
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <a href="{{ route('admin.cinema.show', $studio->cinema) }}"
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                    justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                    transition-all duration-200 shadow-sm flex-shrink-0 mt-0.5">
                    <i class="ti ti-arrow-left text-base"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-black text-gray-900">
                            {{ $studio->name }}
                        </h1>
                        @php
                            $tc =
                                [
                                    'imax' => 'bg-blue-50 border-blue-100 text-blue-700',
                                    '4dx' => 'bg-purple-50 border-purple-100 text-purple-700',
                                    '3d' => 'bg-cyan-50 border-cyan-100 text-cyan-700',
                                    'vip' => 'bg-amber-50 border-amber-100 text-amber-700',
                                    'premiere' => 'bg-rose-50 border-rose-100 text-rose-700',
                                    'regular' => 'bg-gray-100 border-gray-200 text-gray-600',
                                ][$studio->type] ?? 'bg-gray-100 border-gray-200 text-gray-600';
                        @endphp
                        <span
                            class="text-xs font-bold px-2.5 py-1 rounded-full border
                        {{ $tc }}">
                            {{ strtoupper($studio->type) }}
                        </span>
                        <span
                            class="text-xs font-semibold px-2.5 py-1 rounded-full
                        {{ $studio->is_active
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                            {{ $studio->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                        <i class="ti ti-building text-xs text-gray-400"></i>
                        <a href="{{ route('admin.cinema.show', $studio->cinema) }}"
                            class="hover:text-gray-700 transition">
                            {{ $studio->cinema->name }}
                        </a>
                        <span class="text-gray-300">·</span>
                        {{ $studio->cinema->city }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="regenOpen = !regenOpen"
                    class="h-9 px-3 border border-gray-200 text-gray-500 text-xs
                    font-medium rounded-xl hover:bg-gray-50 transition-all
                    duration-200 flex items-center gap-1.5">
                    <i class="ti ti-refresh text-sm"></i>
                    Regenerate
                </button>
                <form method="POST" action="{{ route('admin.studios.toggle', $studio) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="h-9 px-3 border border-gray-200 text-gray-500 text-xs
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center gap-1.5">
                        <i
                            class="ti {{ $studio->is_active ? 'ti-eye-off' : 'ti-eye' }} text-sm"></i>
                        {{ $studio->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <a href="{{ route('admin.studios.edit', $studio) }}"
                    class="h-9 px-4 bg-gray-900 hover:bg-gray-800 text-white text-xs
                    font-semibold rounded-xl transition-all duration-200
                    flex items-center gap-1.5 shadow-sm">
                    <i class="ti ti-edit text-sm"></i>
                    Edit
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
            @foreach ([['label' => 'Total Kursi', 'value' => $seatStats['bookable'], 'color' => 'text-gray-900'], ['label' => 'Regular', 'value' => $seatStats['regular'], 'color' => 'text-emerald-600'], ['label' => 'VIP', 'value' => $seatStats['vip'], 'color' => 'text-violet-600'], ['label' => 'Couple', 'value' => $seatStats['couple'], 'color' => 'text-pink-600'], ['label' => 'Diblokir', 'value' => $seatStats['blocked'], 'color' => 'text-gray-400']] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 text-center
            shadow-sm">
                    <p class="text-2xl font-black {{ $stat['color'] }}">
                        {{ $stat['value'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Regenerate form --}}
        <div x-show="regenOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <div class="flex items-start gap-3 mb-4">
                <i class="ti ti-alert-triangle text-amber-600 text-xl flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-amber-800 mb-0.5">
                        Regenerate Layout Kursi
                    </p>
                    <p class="text-xs text-amber-600">
                        Semua data seat layout lama akan dihapus dan dibuat ulang.
                        Pastikan tidak ada jadwal aktif di studio ini.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.studios.regenerate', $studio) }}"
                onsubmit="return confirm('REGENERATE layout kursi?\n\nSemua data kursi lama akan dihapus!')"
                class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-amber-800 mb-1.5">
                        Baris <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="rows" min="1" max="26" value="{{ $studio->rows }}"
                        class="w-full bg-white border border-amber-300 text-gray-900
                        text-sm rounded-xl px-3 h-10 outline-none
                        focus:border-amber-500 transition-all duration-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-800 mb-1.5">
                        Kolom <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="cols" min="1" max="40" value="{{ $studio->cols }}"
                        class="w-full bg-white border border-amber-300 text-gray-900
                        text-sm rounded-xl px-3 h-10 outline-none
                        focus:border-amber-500 transition-all duration-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-800 mb-1.5">
                        Baris VIP
                    </label>
                    <input type="text" name="vip_rows" placeholder="A,B"
                        class="w-full bg-white border border-amber-300 text-gray-900
                        text-sm rounded-xl px-3 h-10 outline-none
                        focus:border-amber-500 transition-all duration-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-800 mb-1.5">
                        Aisle Setelah
                    </label>
                    <input type="text" name="aisle_after" placeholder="5,12"
                        class="w-full bg-white border border-amber-300 text-gray-900
                        text-sm rounded-xl px-3 h-10 outline-none
                        focus:border-amber-500 transition-all duration-200">
                </div>
                <div class="col-span-2 sm:col-span-4 flex gap-2">
                    <button type="submit"
                        class="h-10 px-5 bg-amber-600 hover:bg-amber-700 text-white
                        text-sm font-semibold rounded-xl transition-all duration-200
                        flex items-center gap-2">
                        <i class="ti ti-refresh text-base"></i>
                        Regenerate Layout
                    </button>
                    <button type="button" @click="regenOpen = false"
                        class="h-10 px-4 border border-amber-300 text-amber-700 text-sm
                        rounded-xl hover:bg-amber-100 transition-all duration-200">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        {{-- Interactive Seat Map --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">
                        Denah Kursi
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Klik kursi untuk mengubah tipenya
                    </p>
                </div>

                {{-- Legend --}}
                <div class="flex items-center gap-3 text-[10px] text-gray-500">
                    @foreach ([['emerald-200 border-emerald-300', 'Regular'], ['violet-200 border-violet-300', 'VIP'], ['pink-200 border-pink-300', 'Couple'], ['gray-100 border-gray-200', 'Blocked'], ['blue-200 border-blue-300', 'Disabled']] as [$bg, $label])
                        <span class="flex items-center gap-1">
                            <span
                                class="w-3.5 h-3.5 bg-{{ $bg }} rounded-[2px]
                        border border-{{ $bg }}"></span>
                            {{ $label }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="p-5 overflow-x-auto" x-data="seatEditor({{ $studio->id }})">

                {{-- Screen --}}
                <div class="text-center mb-5 max-w-xs mx-auto">
                    <div
                        class="h-1.5 bg-gradient-to-r from-transparent via-gray-400
                    to-transparent rounded-full">
                    </div>
                    <p class="text-[9px] text-gray-400 mt-1 tracking-widest uppercase">
                        Layar
                    </p>
                </div>

                {{-- Seat grid --}}
                <div class="space-y-1.5 min-w-max mx-auto">
                    @foreach ($seatMap as $rowLabel => $seats)
                        <div class="flex items-center gap-1.5">
                            <span
                                class="text-[10px] text-gray-400 w-5 text-right
                        flex-shrink-0 font-medium">
                                {{ $rowLabel }}
                            </span>
                            <div class="flex gap-1">
                                @php $lastCol = 0; @endphp
                                @foreach ($seats as $seat)
                                    {{-- Aisle gap --}}
                                    @if ($seat->col_number > $lastCol + 1 && $lastCol > 0)
                                        <div class="w-4"></div>
                                    @endif
                                    @php $lastCol = $seat->col_number; @endphp

                                    <button type="button"
                                        @click="editSeat({{ $seat->id }}, '{{ $seat->seat_number }}')"
                                        :class="getSeatClass({{ $seat->id }}, '{{ $seat->seat_type }}')"
                                        class="w-7 h-7 rounded-md text-[8px] font-bold border
                                transition-all duration-150 hover:scale-110
                                hover:shadow-sm cursor-pointer select-none
                                relative flex items-center justify-center"
                                        :title="'Kursi ' + '{{ $seat->seat_number }}' + ' — ' + getSeatType(
                                            {{ $seat->id }}, '{{ $seat->seat_type }}')">
                                        <span class="opacity-60">{{ $seat->col_number }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <span
                                class="text-[10px] text-gray-400 w-5 text-left
                        flex-shrink-0">
                                {{ $rowLabel }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Edit modal --}}
                <div x-show="editOpen" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    @click.away="editOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(0,0,0,0.4)">
                    <div class="bg-white rounded-2xl shadow-2xl p-5 w-72">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-bold text-gray-900">
                                Ubah Tipe Kursi
                                <span class="text-gray-500 font-normal ml-1" x-text="editingSeatNumber">
                                </span>
                            </p>
                            <button @click="editOpen = false"
                                class="w-7 h-7 flex items-center justify-center
                                text-gray-400 hover:text-gray-600 rounded-lg
                                hover:bg-gray-100 transition">
                                <i class="ti ti-x text-sm"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ([['regular', 'Regular', 'bg-emerald-50 border-emerald-200 text-emerald-700'], ['vip', 'VIP', 'bg-violet-50 border-violet-200 text-violet-700'], ['couple', 'Couple', 'bg-pink-50 border-pink-200 text-pink-700'], ['disabled', 'Disabled', 'bg-blue-50 border-blue-200 text-blue-700'], ['blocked', 'Blocked', 'bg-gray-100 border-gray-200 text-gray-600']] as [$val, $label, $cls])
                                <button @click="saveSeat('{{ $val }}')"
                                    :class="editingType === '{{ $val }}'
                                        ?
                                        'ring-2 ring-gray-900 ring-offset-1' :
                                        ''"
                                    class="p-3 border rounded-xl text-sm font-semibold
                                transition-all duration-150 hover:scale-105
                                {{ $cls }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <div x-show="saving" class="mt-3 text-center">
                            <p
                                class="text-xs text-gray-500 flex items-center
                            justify-center gap-1.5">
                                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                                    </path>
                                </svg>
                                Menyimpan...
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Upcoming Showtimes --}}
        @if ($showtimes->count() > 0)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Jadwal Mendatang</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($showtimes as $st)
                        <div class="flex items-center gap-4 px-5 py-3">
                            <div
                                class="w-10 h-14 bg-gray-100 rounded-xl overflow-hidden
                    flex-shrink-0">
                                <img src="{{ $st->movie->poster_url }}" class="w-full h-full object-cover"
                                    alt="{{ $st->movie->title }}" loading="lazy">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $st->movie->title }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $st->start_time->translatedFormat('D, d M Y — H:i') }}
                                    · {{ $st->format_label }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs font-semibold text-gray-700">
                                    {{ $st->available_seats }} kursi tersedia
                                </p>
                                <span
                                    class="text-[10px] font-medium px-2 py-0.5 rounded-full
                        {{ $st->status === 'open' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ ucfirst($st->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Danger zone --}}
        <div class="bg-white border border-red-100 rounded-2xl p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-red-600 mb-3 flex items-center gap-2">
                <i class="ti ti-alert-triangle text-base"></i>
                Zona Berbahaya
            </h3>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Hapus Studio</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Menghapus studio akan menghapus semua seat layout secara permanen.
                        Studio tidak dapat dihapus jika memiliki jadwal aktif.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.studios.destroy', $studio) }}"
                    onsubmit="return confirm('Hapus studio {{ $studio->name }}?\nSemua data layout kursi akan ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-9 px-4 bg-red-50
                        border border-red-200 text-red-600 text-xs font-semibold
                        rounded-xl hover:bg-red-100 transition-all duration-200
                        flex-shrink-0">
                        <i class="ti ti-trash text-sm"></i>
                        Hapus Studio
                    </button>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function seatEditor(studioId) {
                return {
                    editOpen: false,
                    editingSeatId: null,
                    editingSeatNumber: '',
                    editingType: '',
                    saving: false,
                    seatTypes: {},

                    editSeat(seatId, seatNumber) {
                        this.editingSeatId = seatId;
                        this.editingSeatNumber = seatNumber;
                        this.editingType = this.seatTypes[seatId] || 'regular';
                        this.editOpen = true;
                    },

                    async saveSeat(type) {
                        this.saving = true;
                        this.editingType = type;

                        try {
                            const res = await fetch(
                                `/admin/studios/${studioId}/seat`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        seat_layout_id: this.editingSeatId,
                                        seat_type: type,
                                    }),
                                }
                            );
                            const data = await res.json();
                            if (data.success) {
                                this.seatTypes[this.editingSeatId] = type;
                                this.editOpen = false;
                            }
                        } catch (err) {
                            console.error(err);
                        } finally {
                            this.saving = false;
                        }
                    },

                    getSeatType(seatId, defaultType) {
                        return this.seatTypes[seatId] || defaultType;
                    },

                    getSeatClass(seatId, defaultType) {
                        const t = this.getSeatType(seatId, defaultType);
                        return {
                            'bg-emerald-100 border-emerald-300 text-emerald-700': t === 'regular',
                            'bg-violet-100 border-violet-300 text-violet-700': t === 'vip',
                            'bg-pink-100 border-pink-300 text-pink-700': t === 'couple',
                            'bg-gray-100 border-gray-200 text-gray-400': t === 'blocked',
                            'bg-blue-100 border-blue-300 text-blue-600': t === 'disabled',
                        };
                    }
                }
            }
        </script>
    @endpush

</x-app-layout>
