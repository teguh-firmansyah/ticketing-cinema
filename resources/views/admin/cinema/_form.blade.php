{{-- Info Dasar --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 pb-1
        border-b border-gray-100">
        <i class="ti ti-building text-gray-400"></i>
        Informasi Bioskop
    </h3>

    {{-- Logo --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">
            Logo Bioskop
        </label>
        <div class="flex items-start gap-4">
            {{-- Preview --}}
            <div class="w-16 h-16 bg-gray-100 border-2 border-dashed border-gray-200
                rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden"
                id="logo-preview">
                @if (isset($cinema) && $cinema->logo)
                    <img src="{{ Storage::url($cinema->logo) }}" class="w-full h-full object-contain" alt="Logo">
                @else
                    <i class="ti ti-building text-gray-300 text-2xl" id="logo-placeholder"></i>
                @endif
            </div>
            <div class="flex-1">
                <input type="file" name="logo" id="logo" accept="image/*" class="hidden"
                    onchange="previewLogo(this)">
                <label for="logo"
                    class="inline-flex items-center gap-2 h-9 px-4 bg-white border
                        border-gray-200 text-gray-600 text-xs font-medium rounded-xl
                        cursor-pointer hover:bg-gray-50 hover:border-gray-300
                        transition-all duration-200">
                    <i class="ti ti-upload text-sm"></i>
                    Pilih Logo
                </label>
                <p class="text-xs text-gray-400 mt-1.5">
                    PNG, JPG, WebP · Maks. 2MB
                </p>
                @error('logo')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Nama --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            Nama Bioskop <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $cinema->name ?? '') }}"
            placeholder="cth. CGV Grand Indonesia"
            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                rounded-xl px-4 h-11 outline-none focus:border-gray-400
                focus:bg-white transition-all duration-200
                @error('name') border-red-400 @enderror">
        @error('name')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kota + Alamat --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Kota <span class="text-red-500">*</span>
            </label>
            <input type="text" name="city" value="{{ old('city', $cinema->city ?? '') }}"
                placeholder="cth. Jakarta" list="city-suggestions"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200
                    @error('city') border-red-400 @enderror">
            <datalist id="city-suggestions">
                @foreach (['Jakarta', 'Surabaya', 'Bandung', 'Yogyakarta', 'Medan', 'Makassar', 'Semarang', 'Bali', 'Palembang', 'Malang'] as $c)
                    <option value="{{ $c }}">
                @endforeach
            </datalist>
            @error('city')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Alamat Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="address" value="{{ old('address', $cinema->address ?? '') }}"
                placeholder="Nama mall, lantai, jalan, nomor"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200
                    @error('address') border-red-400 @enderror">
            @error('address')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Phone + Email --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Nomor Telepon
            </label>
            <input type="text" name="phone" value="{{ old('phone', $cinema->phone ?? '') }}"
                placeholder="021-XXXXXXX"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Email
            </label>
            <input type="email" name="email" value="{{ old('email', $cinema->email ?? '') }}"
                placeholder="info@bioskop.com"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            Deskripsi
        </label>
        <textarea name="description" rows="3" placeholder="Deskripsi singkat tentang bioskop ini..."
            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                rounded-xl px-4 py-3 outline-none focus:border-gray-400
                focus:bg-white transition-all duration-200 resize-none">{{ old('description', $cinema->description ?? '') }}</textarea>
    </div>
</div>

{{-- Lokasi --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 pb-1
        border-b border-gray-100">
        <i class="ti ti-map-pin text-gray-400"></i>
        Lokasi & Maps
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Latitude
            </label>
            <input type="number" name="latitude" step="0.0000001"
                value="{{ old('latitude', $cinema->latitude ?? '') }}" placeholder="-6.1234567"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Longitude
            </label>
            <input type="number" name="longitude" step="0.0000001"
                value="{{ old('longitude', $cinema->longitude ?? '') }}" placeholder="106.8234567"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            URL Google Maps
        </label>
        <div
            class="flex items-center gap-2 bg-gray-50 border border-gray-200
            rounded-xl px-3 h-11 focus-within:border-gray-400 focus-within:bg-white
            transition-all duration-200">
            <i class="ti ti-map-2 text-gray-400 text-sm flex-shrink-0"></i>
            <input type="url" name="maps_url" value="{{ old('maps_url', $cinema->maps_url ?? '') }}"
                placeholder="https://maps.google.com/?q=..."
                class="bg-transparent border-none outline-none text-sm text-gray-900
                    placeholder-gray-400 w-full">
        </div>
    </div>
</div>

{{-- Fasilitas --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 pb-3
        border-b border-gray-100 mb-4">
        <i class="ti ti-star text-gray-400"></i>
        Fasilitas
    </h3>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach ([['value' => 'parking', 'icon' => 'ti-car', 'label' => 'Parkir'], ['value' => 'food_court', 'icon' => 'ti-tools-kitchen', 'label' => 'Food Court'], ['value' => 'atm', 'icon' => 'ti-building-bank', 'label' => 'ATM'], ['value' => 'wifi', 'icon' => 'ti-wifi', 'label' => 'WiFi Gratis'], ['value' => 'prayer_room', 'icon' => 'ti-building-mosque', 'label' => 'Musholla'], ['value' => 'nursing_room', 'icon' => 'ti-heart', 'label' => 'Ruang Ibu'], ['value' => 'handicap_access', 'icon' => 'ti-accessible', 'label' => 'Akses Difabel'], ['value' => 'vip_lounge', 'icon' => 'ti-armchair', 'label' => 'VIP Lounge']] as $fac)
            @php
                $checked = in_array($fac['value'], old('facilities', $cinema->facilities ?? []));
            @endphp
            <label
                class="flex items-center gap-2.5 p-3 border rounded-xl cursor-pointer
            transition-all duration-150 hover:border-gray-300
            {{ $checked ? 'border-gray-900 bg-gray-50' : 'border-gray-200 bg-white' }}">
                <input type="checkbox" name="facilities[]" value="{{ $fac['value'] }}"
                    {{ $checked ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 accent-gray-900">
                <i
                    class="ti {{ $fac['icon'] }} text-sm
                {{ $checked ? 'text-gray-700' : 'text-gray-400' }}"></i>
                <span class="text-xs font-medium
                {{ $checked ? 'text-gray-800' : 'text-gray-500' }}">
                    {{ $fac['label'] }}
                </span>
            </label>
        @endforeach
    </div>
</div>

{{-- Pengaturan --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 pb-1
        border-b border-gray-100">
        <i class="ti ti-settings text-gray-400"></i>
        Pengaturan
    </h3>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Urutan Tampil
            </label>
            <input type="number" name="order" min="0" value="{{ old('order', $cinema->order ?? 0) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm
                    rounded-xl px-4 h-11 outline-none focus:border-gray-400
                    focus:bg-white transition-all duration-200">
            <p class="text-xs text-gray-400 mt-1">
                0 = paling atas
            </p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                Status
            </label>
            <div class="flex items-center gap-3 h-11">
                <label class="flex items-center gap-2 cursor-pointer">
                    <div class="relative" x-data="{ on: {{ old('is_active', $cinema->is_active ?? true) ? 'true' : 'false' }} }">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="on"
                            {{ old('is_active', $cinema->is_active ?? true) ? 'checked' : '' }} class="sr-only">
                        <div @click="on = !on" :class="on ? 'bg-gray-900' : 'bg-gray-200'"
                            class="w-11 h-6 rounded-full transition-colors duration-200
                                cursor-pointer relative">
                            <div :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                                class="absolute top-0.5 w-5 h-5 bg-white rounded-full
                                    shadow-sm transition-transform duration-200">
                            </div>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Aktif</span>
                </label>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function previewLogo(input) {
            const preview = document.getElementById('logo-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}"
                class="w-full h-full object-contain" alt="Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Auto-fill slug dari nama
        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
            });
        }
    </script>
@endpush
