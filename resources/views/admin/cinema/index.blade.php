<x-app-layout>
    <x-slot name="header">Kelola Bioskop</x-slot>

    <div class="p-6 space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([['label' => 'Total Bioskop', 'value' => $stats['total'], 'icon' => 'ti-building', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'], ['label' => 'Aktif', 'value' => $stats['active'], 'icon' => 'ti-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'], ['label' => 'Total Studio', 'value' => $stats['total_studios'], 'icon' => 'ti-door', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50'], ['label' => 'Total Kursi', 'value' => number_format($stats['total_seats']), 'icon' => 'ti-armchair', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50']] as $stat)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <div
                            class="w-9 h-9 {{ $stat['bg'] }} rounded-xl flex items-center
                    justify-center">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-lg"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200
        text-emerald-700 text-sm px-4 py-3 rounded-xl"
                x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="flex items-center gap-3 bg-red-50 border border-red-200
        text-red-700 text-sm px-4 py-3 rounded-xl">
                <i class="ti ti-circle-x text-base flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center
        justify-between gap-3">

            {{-- Search + Filter --}}
            <form method="GET" class="flex gap-2 flex-1 max-w-lg">
                <div
                    class="flex items-center gap-2 bg-white border border-gray-200
                rounded-xl px-3 h-10 flex-1 focus-within:border-gray-400
                transition-colors duration-200 shadow-sm">
                    <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama, kota, alamat..."
                        class="bg-transparent border-none outline-none text-sm
                        text-gray-700 placeholder-gray-400 w-full">
                    @if ($search)
                        <a href="{{ route('admin.cinema.index') }}"
                            class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
                            <i class="ti ti-x text-xs"></i>
                        </a>
                    @endif
                </div>
                <select name="city" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 text-gray-700 text-sm
                    rounded-xl px-3 h-10 outline-none cursor-pointer shadow-sm
                    focus:border-gray-400">
                    <option value="">Semua Kota</option>
                    @foreach ($cities as $c)
                        <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>
                            {{ $c }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.cinema.create') }}"
                class="inline-flex items-center gap-2 h-10 px-5 bg-gray-900
                hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                transition-all duration-200 shadow-sm flex-shrink-0">
                <i class="ti ti-plus text-base"></i>
                Tambah Bioskop
            </a>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th
                                class="text-left px-5 py-3.5 text-xs font-semibold
                            text-gray-500 uppercase tracking-wider">
                                Bioskop
                            </th>
                            <th
                                class="text-left px-5 py-3.5 text-xs font-semibold
                            text-gray-500 uppercase tracking-wider">
                                Kota
                            </th>
                            <th
                                class="text-center px-5 py-3.5 text-xs font-semibold
                            text-gray-500 uppercase tracking-wider">
                                Studio
                            </th>
                            <th
                                class="text-center px-5 py-3.5 text-xs font-semibold
                            text-gray-500 uppercase tracking-wider">
                                Kursi
                            </th>
                            <th
                                class="text-center px-5 py-3.5 text-xs font-semibold
                            text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="text-right px-5 py-3.5 text-xs font-semibold
                            text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($cinemas as $cinema)
                            <tr class="hover:bg-gray-50/50 transition-colors duration-150 group">

                                {{-- Bioskop --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 bg-gray-100 rounded-xl flex items-center
                                    justify-center flex-shrink-0 border border-gray-200">
                                            @if ($cinema->logo)
                                                <img src="{{ Storage::url($cinema->logo) }}"
                                                    class="w-7 h-7 object-contain" alt="{{ $cinema->name }}">
                                            @else
                                                <i class="ti ti-building text-gray-400 text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $cinema->name }}
                                            </p>
                                            <p class="text-xs text-gray-400 truncate max-w-[200px]">
                                                {{ Str::limit($cinema->address, 40) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kota --}}
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs
                                font-medium text-gray-600 bg-gray-100 px-2.5 py-1
                                rounded-full">
                                        <i class="ti ti-map-pin text-xs text-gray-400"></i>
                                        {{ $cinema->city }}
                                    </span>
                                </td>

                                {{-- Studio count --}}
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-semibold text-gray-700">
                                        {{ $cinema->studios_count }}
                                    </span>
                                    <span class="text-xs text-gray-400 ml-1">studio</span>
                                </td>

                                {{-- Total kursi --}}
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-semibold text-gray-700">
                                        {{ number_format($cinema->studios_sum_total_seats ?? 0) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 text-center">
                                    <form method="POST" action="{{ route('admin.cinema.toggle', $cinema) }}"
                                        class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 text-xs
                                        font-semibold px-3 py-1.5 rounded-full
                                        transition-all duration-200
                                        {{ $cinema->is_active
                                            ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'
                                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200' }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full
                                        {{ $cinema->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}">
                                            </span>
                                            {{ $cinema->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center justify-end gap-1.5
                                opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                        <a href="{{ route('admin.cinema.show', $cinema) }}"
                                            class="w-8 h-8 bg-gray-100 hover:bg-blue-50
                                        hover:text-blue-600 rounded-lg flex items-center
                                        justify-center text-gray-500 transition-all duration-150"
                                            title="Detail">
                                            <i class="ti ti-eye text-sm"></i>
                                        </a>
                                        <a href="{{ route('admin.cinema.edit', $cinema) }}"
                                            class="w-8 h-8 bg-gray-100 hover:bg-amber-50
                                        hover:text-amber-600 rounded-lg flex items-center
                                        justify-center text-gray-500 transition-all duration-150"
                                            title="Edit">
                                            <i class="ti ti-edit text-sm"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.cinema.destroy', $cinema) }}"
                                            onsubmit="return confirm('Hapus bioskop {{ $cinema->name }}? Semua studio dan seat layout akan ikut terhapus.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 bg-gray-100 hover:bg-red-50
                                            hover:text-red-600 rounded-lg flex items-center
                                            justify-center text-gray-500
                                            transition-all duration-150"
                                                title="Hapus">
                                                <i class="ti ti-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <i class="ti ti-building-off text-4xl text-gray-300 block mb-3"></i>
                                    <p class="text-sm font-semibold text-gray-400 mb-1">
                                        Belum ada bioskop
                                    </p>
                                    <p class="text-xs text-gray-300 mb-4">
                                        Mulai dengan menambahkan bioskop pertama
                                    </p>
                                    <a href="{{ route('admin.cinema.create') }}"
                                        class="inline-flex items-center gap-2 h-9 px-4
                                    bg-gray-900 text-white text-xs font-semibold
                                    rounded-xl hover:bg-gray-800
                                    transition-all duration-200">
                                        <i class="ti ti-plus text-sm"></i>
                                        Tambah Bioskop
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($cinemas->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex items-center
            justify-between">
                    <p class="text-xs text-gray-500">
                        Menampilkan {{ $cinemas->firstItem() }}–{{ $cinemas->lastItem() }}
                        dari {{ $cinemas->total() }} bioskop
                    </p>
                    <div class="flex items-center gap-1.5">
                        @if ($cinemas->onFirstPage())
                            <span
                                class="w-8 h-8 flex items-center justify-center border
                    border-gray-200 rounded-lg text-gray-300 cursor-not-allowed text-sm">
                                <i class="ti ti-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $cinemas->previousPageUrl() }}"
                                class="w-8 h-8 flex items-center justify-center border
                        border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50
                        transition-colors duration-150 text-sm">
                                <i class="ti ti-chevron-left"></i>
                            </a>
                        @endif

                        @foreach ($cinemas->getUrlRange(max(1, $cinemas->currentPage() - 2), min($cinemas->lastPage(), $cinemas->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-8 h-8 flex items-center justify-center border rounded-lg
                        text-xs font-medium transition-all duration-150
                        {{ $page === $cinemas->currentPage()
                            ? 'bg-gray-900 border-gray-900 text-white'
                            : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($cinemas->hasMorePages())
                            <a href="{{ $cinemas->nextPageUrl() }}"
                                class="w-8 h-8 flex items-center justify-center border
                        border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50
                        transition-colors duration-150 text-sm">
                                <i class="ti ti-chevron-right"></i>
                            </a>
                        @else
                            <span
                                class="w-8 h-8 flex items-center justify-center border
                    border-gray-200 rounded-lg text-gray-300 cursor-not-allowed text-sm">
                                <i class="ti ti-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
