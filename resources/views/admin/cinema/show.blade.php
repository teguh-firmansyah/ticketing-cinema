<x-app-layout>
    <x-slot name="header">Detail Bioskop</x-slot>

    <div class="p-6 space-y-6" x-data="{ addStudio: false }">

        {{-- Flash --}}
        @foreach (['success' => 'emerald', 'error' => 'red'] as $type => $color)
            @if (session($type))
                <div class="flex items-center gap-3 bg-{{ $color }}-50 border
        border-{{ $color }}-200 text-{{ $color }}-700 text-sm px-4 py-3 rounded-xl"
                    x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    <i
                        class="ti ti-{{ $type === 'success' ? 'circle-check' : 'alert-circle' }}
            text-base flex-shrink-0"></i>
                    {{ session($type) }}
                </div>
            @endif
        @endforeach

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.cinema.index') }}"
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                    justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                    transition-all duration-200 shadow-sm flex-shrink-0 mt-0.5">
                    <i class="ti ti-arrow-left text-base"></i>
                </a>
                <div class="flex items-center gap-3">
                    <div
                        class="w-14 h-14 bg-gray-100 border border-gray-200 rounded-2xl
                    flex items-center justify-center flex-shrink-0">
                        @if ($cinema->logo)
                            <img src="{{ Storage::url($cinema->logo) }}" class="w-10 h-10 object-contain"
                                alt="{{ $cinema->name }}">
                        @else
                            <i class="ti ti-building text-gray-400 text-2xl"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl font-black text-gray-900">
                                {{ $cinema->name }}
                            </h1>
                            <span
                                class="text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $cinema->is_active
                                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                {{ $cinema->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5">
                            <i class="ti ti-map-pin text-xs text-gray-400"></i>
                            {{ $cinema->city }} · {{ $cinema->address }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <form method="POST" action="{{ route('admin.cinema.toggle', $cinema) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="h-9 px-3 border border-gray-200 text-gray-500 text-xs
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center gap-1.5">
                        <i class="ti {{ $cinema->is_active ? 'ti-eye-off' : 'ti-eye' }} text-sm"></i>
                        {{ $cinema->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <a href="{{ route('admin.cinema.edit', $cinema) }}"
                    class="h-9 px-4 bg-gray-900 hover:bg-gray-800 text-white text-xs
                    font-semibold rounded-xl transition-all duration-200
                    flex items-center gap-1.5 shadow-sm">
                    <i class="ti ti-edit text-sm"></i>
                    Edit
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach ([['label' => 'Total Studio', 'value' => $stats['total_studios'], 'icon' => 'ti-door', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], ['label' => 'Studio Aktif', 'value' => $stats['active_studios'], 'icon' => 'ti-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['label' => 'Total Kursi', 'value' => number_format($stats['total_seats']), 'icon' => 'ti-armchair', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50']] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                        <div
                            class="w-8 h-8 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-base"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Info + Facilities + Maps --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Info --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase
                tracking-wider mb-4">Informasi
                </h3>
                <div class="space-y-3">
                    @foreach ([['icon' => 'ti-map-pin', 'label' => 'Alamat', 'value' => $cinema->address], ['icon' => 'ti-building', 'label' => 'Kota', 'value' => $cinema->city], ['icon' => 'ti-phone', 'label' => 'Telepon', 'value' => $cinema->phone ?? '-'], ['icon' => 'ti-mail', 'label' => 'Email', 'value' => $cinema->email ?? '-']] as $info)
                        <div class="flex items-start gap-3">
                            <div
                                class="w-7 h-7 bg-gray-100 rounded-lg flex items-center
                        justify-center flex-shrink-0 mt-0.5">
                                <i class="ti {{ $info['icon'] }} text-gray-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider">
                                    {{ $info['label'] }}
                                </p>
                                <p class="text-sm text-gray-700 mt-0.5 leading-snug">
                                    {{ $info['value'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Fasilitas --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase
                tracking-wider mb-4">Fasilitas
                </h3>
                @if ($cinema->facilities && count($cinema->facilities) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($cinema->facilities as $fac)
                            @php
                                $facMap = [
                                    'parking' => ['ti-car', 'Parkir'],
                                    'food_court' => ['ti-tools-kitchen', 'Food Court'],
                                    'atm' => ['ti-building-bank', 'ATM'],
                                    'wifi' => ['ti-wifi', 'WiFi'],
                                    'prayer_room' => ['ti-building-mosque', 'Musholla'],
                                    'nursing_room' => ['ti-heart', 'Ruang Ibu'],
                                    'handicap_access' => ['ti-accessible', 'Difabel'],
                                    'vip_lounge' => ['ti-armchair', 'VIP Lounge'],
                                ];
                                [$fi, $fl] = $facMap[$fac] ?? ['ti-star', ucfirst(str_replace('_', ' ', $fac))];
                            @endphp
                            <div
                                class="flex items-center gap-1.5 bg-gray-50 border border-gray-200
                    text-gray-600 text-xs font-medium px-2.5 py-1.5 rounded-xl">
                                <i class="ti {{ $fi }} text-gray-400 text-sm"></i>
                                {{ $fl }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada fasilitas</p>
                @endif
            </div>

            {{-- Maps --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase
                tracking-wider mb-4">Lokasi
                </h3>
                @if ($cinema->maps_url)
                    <a href="{{ $cinema->maps_url }}" target="_blank"
                        class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-100
                    rounded-xl hover:bg-blue-100 transition-colors duration-200 mb-3">
                        <div
                            class="w-9 h-9 bg-blue-600 rounded-xl flex items-center
                    justify-center flex-shrink-0">
                            <i class="ti ti-map-2 text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-700">Google Maps</p>
                            <p class="text-xs text-blue-500">Buka lokasi bioskop</p>
                        </div>
                        <i class="ti ti-external-link text-blue-400 ml-auto text-sm"></i>
                    </a>
                @endif
                @if ($cinema->latitude && $cinema->longitude)
                    <div class="space-y-1 text-xs text-gray-500 font-mono">
                        <p>Lat: <span class="text-gray-700">{{ $cinema->latitude }}</span></p>
                        <p>Lng: <span class="text-gray-700">{{ $cinema->longitude }}</span></p>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Koordinat belum diset</p>
                @endif
            </div>

        </div>

        {{-- Studios --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Studio</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $cinema->studios->count() }} studio terdaftar
                    </p>
                </div>
                <button @click="addStudio = !addStudio"
                    class="inline-flex items-center gap-2 h-9 px-4 bg-gray-900
                    hover:bg-gray-800 text-white text-xs font-semibold rounded-xl
                    transition-all duration-200 shadow-sm">
                    <i class="ti text-sm" :class="addStudio ? 'ti-x' : 'ti-plus'"></i>
                    <span x-text="addStudio ? 'Tutup' : 'Tambah Studio'"></span>
                </button>
            </div>

            {{-- Add Studio Form --}}
            <div x-show="addStudio" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="border-b border-gray-100 bg-gray-50/70 p-5">

                <h3 class="text-sm font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <i class="ti ti-door text-gray-400"></i>
                    Form Tambah Studio
                </h3>

                <form method="POST" action="{{ route('admin.cinema.studios.store', $cinema) }}" class="space-y-5">
                    @csrf

                    {{-- Row 1: Nama + Tipe --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Nama Studio <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" placeholder="cth. Studio 1, IMAX, 4DX..."
                                class="w-full bg-white border border-gray-200 text-gray-900
                                text-sm rounded-xl px-4 h-10 outline-none
                                focus:border-gray-400 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Tipe Studio <span class="text-red-500">*</span>
                            </label>
                            <select name="type"
                                class="w-full bg-white border border-gray-200 text-gray-900
                                text-sm rounded-xl px-3 h-10 outline-none cursor-pointer
                                focus:border-gray-400 transition-all duration-200">
                                @foreach ([
        'regular' => 'Regular',
        '3d' => '3D',
        'imax' => 'IMAX',
        '4dx' => '4DX',
        'vip' => 'VIP',
        'premiere' => 'Premiere',
    ] as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Layout --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl">
                        <p
                            class="text-xs font-semibold text-gray-700 mb-3
                        flex items-center gap-1.5">
                            <i class="ti ti-layout-grid text-gray-400 text-sm"></i>
                            Konfigurasi Layout Kursi
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Jumlah Baris
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="rows" min="1" max="26" value="8"
                                    placeholder="cth. 8"
                                    class="w-full bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400
                                    transition-all duration-200">
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Maks. 26 (A–Z)
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Jumlah Kolom
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="cols" min="1" max="40" value="16"
                                    placeholder="cth. 16"
                                    class="w-full bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400
                                    transition-all duration-200">
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Maks. 40
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Baris VIP
                                </label>
                                <input type="text" name="vip_rows" placeholder="cth. A,B"
                                    class="w-full bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400
                                    transition-all duration-200">
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Pisah koma
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Baris Couple
                                </label>
                                <input type="text" name="couple_rows" placeholder="cth. H"
                                    class="w-full bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400
                                    transition-all duration-200">
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Pisah koma
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Gang Setelah Kolom ke-
                                </label>
                                <input type="text" name="aisle_after"
                                    placeholder="cth. 4,12 (setelah kolom 4 & 12)"
                                    class="w-full bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400
                                    transition-all duration-200">
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Nomor kolom yang membentuk gang (aisle)
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Urutan Tampil
                                </label>
                                <input type="number" name="order" min="0"
                                    value="{{ $cinema->studios->count() + 1 }}"
                                    class="w-full bg-gray-50 border border-gray-200
                                    text-gray-900 text-sm rounded-xl px-3 h-10
                                    outline-none focus:border-gray-400
                                    transition-all duration-200">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" checked
                                        class="w-4 h-4 rounded border-gray-300 accent-gray-900">
                                    <span class="text-sm font-medium text-gray-700">
                                        Aktif
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Fasilitas Studio --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">
                            Fasilitas Studio
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ([
        'dolby_stereo' => 'Dolby Stereo',
        'dolby_atmos' => 'Dolby Atmos',
        '4dx_motion' => '4DX Motion',
        'water_effect' => 'Water Effect',
        'wind_effect' => 'Wind Effect',
        'scent' => 'Scent',
        'imax_laser' => 'IMAX Laser',
        '4k_projection' => '4K Projection',
        '3d_projection' => '3D Projection',
        'recliner' => 'Recliner',
        'food_service' => 'Food Service',
        'private_lounge' => 'Private Lounge',
    ] as $val => $label)
                                <label
                                    class="flex items-center gap-1.5 bg-white border
                            border-gray-200 text-gray-600 text-xs font-medium
                            px-2.5 py-1.5 rounded-xl cursor-pointer
                            hover:border-gray-400 has-[:checked]:border-gray-900
                            has-[:checked]:bg-gray-900 has-[:checked]:text-white
                            transition-all duration-150">
                                    <input type="checkbox" name="facilities[]" value="{{ $val }}"
                                        class="sr-only">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <button type="submit"
                            class="inline-flex items-center gap-2 h-10 px-5 bg-gray-900
                            hover:bg-gray-800 text-white text-sm font-semibold
                            rounded-xl transition-all duration-200 shadow-sm">
                            <i class="ti ti-plus text-base"></i>
                            Tambah Studio
                        </button>
                        <button type="button" @click="addStudio = false"
                            class="h-10 px-4 border border-gray-200 text-gray-600 text-sm
                            rounded-xl hover:bg-gray-50 transition-all duration-200">
                            Batal
                        </button>
                        <p class="text-xs text-gray-400 ml-2">
                            <i class="ti ti-info-circle text-sm"></i>
                            Layout kursi akan digenerate otomatis
                        </p>
                    </div>
                </form>
            </div>

            {{-- Studios list --}}
            @if ($cinema->studios->isEmpty())
                <div class="text-center py-14">
                    <i class="ti ti-door-off text-4xl text-gray-300 block mb-3"></i>
                    <p class="text-sm font-semibold text-gray-400 mb-1">
                        Belum ada studio
                    </p>
                    <p class="text-xs text-gray-300 mb-4">
                        Tambah studio pertama dengan tombol di atas
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach ($cinema->studios->sortBy('order') as $studio)
                        <div
                            class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/50
                transition-colors duration-150 group">

                            {{-- Type icon --}}
                            @php
                                $typeConfig = [
                                    'imax' => [
                                        'bg' => 'bg-blue-50',
                                        'text' => 'text-blue-600',
                                        'border' => 'border-blue-100',
                                    ],
                                    '4dx' => [
                                        'bg' => 'bg-purple-50',
                                        'text' => 'text-purple-600',
                                        'border' => 'border-purple-100',
                                    ],
                                    '3d' => [
                                        'bg' => 'bg-cyan-50',
                                        'text' => 'text-cyan-600',
                                        'border' => 'border-cyan-100',
                                    ],
                                    'vip' => [
                                        'bg' => 'bg-amber-50',
                                        'text' => 'text-amber-600',
                                        'border' => 'border-amber-100',
                                    ],
                                    'premiere' => [
                                        'bg' => 'bg-rose-50',
                                        'text' => 'text-rose-600',
                                        'border' => 'border-rose-100',
                                    ],
                                    'regular' => [
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-500',
                                        'border' => 'border-gray-200',
                                    ],
                                ];
                                $tc = $typeConfig[$studio->type] ?? $typeConfig['regular'];
                            @endphp
                            <div
                                class="w-11 h-11 {{ $tc['bg'] }} border {{ $tc['border'] }}
                    rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-[10px] font-black {{ $tc['text'] }} tracking-wider">
                                    {{ strtoupper($studio->type) }}
                                </span>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $studio->name }}
                                    </p>
                                    @if (!$studio->is_active)
                                        <span
                                            class="text-[10px] font-medium bg-gray-100
                            text-gray-400 px-2 py-0.5 rounded-full">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <i class="ti ti-layout-grid text-xs"></i>
                                        {{ $studio->rows }} baris × {{ $studio->cols }} kolom
                                    </span>
                                    <span>·</span>
                                    <span class="flex items-center gap-1 font-medium text-gray-600">
                                        <i class="ti ti-armchair text-xs"></i>
                                        {{ number_format($studio->total_seats) }} kursi
                                    </span>
                                    @if ($studio->facilities && count($studio->facilities) > 0)
                                        <span>·</span>
                                        <span class="truncate max-w-[180px]">
                                            {{ implode(', ', array_slice($studio->facilities, 0, 3)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Seat layout preview --}}
                            <div class="hidden xl:flex flex-col gap-0.5 flex-shrink-0">
                                @foreach ($studio->seatLayouts->groupBy('row_label')->take(4) as $row => $seats)
                                    <div class="flex gap-0.5">
                                        @foreach ($seats->take(12) as $seat)
                                            <div
                                                class="w-2 h-2 rounded-[1px]
                            {{ $seat->seat_type === 'blocked'
                                ? 'bg-transparent'
                                : ($seat->seat_type === 'vip' || $seat->seat_type === 'couple'
                                    ? 'bg-violet-300'
                                    : 'bg-emerald-300') }}">
                                            </div>
                                        @endforeach
                                        @if ($seats->count() > 12)
                                            <span class="text-[8px] text-gray-400 leading-none self-center ml-0.5">
                                                +{{ $seats->count() - 12 }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                                @if ($studio->seatLayouts->groupBy('row_label')->count() > 4)
                                    <p class="text-[8px] text-gray-400">
                                        +{{ $studio->seatLayouts->groupBy('row_label')->count() - 4 }} baris
                                    </p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div
                                class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100
                    transition-opacity duration-150 flex-shrink-0">
                                {{-- Toggle active --}}
                                <form method="POST"
                                    action="{{ route('admin.cinema.studios.toggle', [$cinema, $studio]) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="w-8 h-8 border border-gray-200 rounded-lg
                                flex items-center justify-center text-gray-400
                                hover:border-gray-400 hover:text-gray-600
                                transition-all duration-150"
                                        title="{{ $studio->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="ti {{ $studio->is_active ? 'ti-eye-off' : 'ti-eye' }} text-sm"></i>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form method="POST"
                                    action="{{ route('admin.cinema.studios.destroy', [$cinema, $studio]) }}"
                                    onsubmit="return confirm('Hapus studio {{ $studio->name }}? Semua data layout kursi akan ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 border border-gray-200 rounded-lg
                                flex items-center justify-center text-gray-400
                                hover:border-red-300 hover:text-red-500
                                hover:bg-red-50 transition-all duration-150"
                                        title="Hapus Studio">
                                        <i class="ti ti-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- Danger zone --}}
        <div class="bg-white border border-red-100 rounded-2xl p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-red-600 mb-3 flex items-center gap-2">
                <i class="ti ti-alert-triangle text-base"></i>
                Zona Berbahaya
            </h3>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Hapus Bioskop</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Menghapus bioskop akan menghapus semua studio, seat layout,
                        dan data terkait secara permanen.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.cinema.destroy', $cinema) }}"
                    onsubmit="return confirm('HAPUS PERMANEN bioskop {{ $cinema->name }}?\n\nSemua studio dan data terkait akan ikut terhapus.\nTindakan ini TIDAK DAPAT DIBATALKAN.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-9 px-4 bg-red-50
                        border border-red-200 text-red-600 text-xs font-semibold
                        rounded-xl hover:bg-red-100 transition-all duration-200
                        flex-shrink-0">
                        <i class="ti ti-trash text-sm"></i>
                        Hapus Bioskop
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
