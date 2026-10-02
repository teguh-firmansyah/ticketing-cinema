<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Studio;
use App\Models\SeatLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CinemaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $city   = $request->get('city', '');

        $query = Cinema::withCount(['studios'])
            ->withSum('studios', 'total_seats')
            ->with(['studios' => fn($q) => $q->orderBy('order')])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',    'like', "%{$search}%")
                    ->orWhere('city',  'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($city) {
            $query->where('city', $city);
        }

        $cinemas = $query->paginate(10)->withQueryString();

        $cities = Cinema::distinct('city')
            ->orderBy('city')
            ->pluck('city');

        $stats = [
            'total'        => Cinema::count(),
            'active'       => Cinema::where('is_active', true)->count(),
            'total_studios' => Studio::count(),
            'total_seats'  => Studio::sum('total_seats'),
        ];

        return view('admin.cinema.index', compact(
            'cinemas',
            'cities',
            'stats',
            'search',
            'city'
        ));
    }

    public function create()
    {
        return view('admin.cinema.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'city'        => 'required|string|max:100',
            'address'     => 'required|string|max:300',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'description' => 'nullable|string|max:1000',
            'facilities'  => 'nullable|array',
            'facilities.*' => 'string',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'maps_url'    => 'nullable|url|max:500',
            'is_active'   => 'boolean',
            'order'       => 'integer|min:0',
            'logo'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')
                ->store('cinemas/logos', 'public');
        }

        $cinema = Cinema::create($validated);

        return redirect()
            ->route('admin.cinema.show', $cinema)
            ->with('success', "Bioskop {$cinema->name} berhasil ditambahkan.");
    }

    public function show(Cinema $cinema)
    {
        $cinema->load([
            'studios' => fn($q) => $q->orderBy('order'),
            'studios.seatLayouts',
        ]);

        $stats = [
            'total_studios' => $cinema->studios->count(),
            'total_seats'   => $cinema->studios->sum('total_seats'),
            'active_studios' => $cinema->studios->where('is_active', true)->count(),
        ];

        return view('admin.cinema.show', compact('cinema', 'stats'));
    }

    public function edit(Cinema $cinema)
    {
        return view('admin.cinema.edit', compact('cinema'));
    }

    public function update(Request $request, Cinema $cinema)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'city'        => 'required|string|max:100',
            'address'     => 'required|string|max:300',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'description' => 'nullable|string|max:1000',
            'facilities'  => 'nullable|array',
            'facilities.*' => 'string',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'maps_url'    => 'nullable|url|max:500',
            'is_active'   => 'boolean',
            'order'       => 'integer|min:0',
            'logo'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        // Handle logo
        if ($request->hasFile('logo')) {
            if ($cinema->logo) {
                Storage::disk('public')->delete($cinema->logo);
            }
            $validated['logo'] = $request->file('logo')
                ->store('cinemas/logos', 'public');
        }

        // Update slug jika nama berubah
        if ($validated['name'] !== $cinema->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $cinema->update($validated);

        return redirect()
            ->route('admin.cinema.show', $cinema)
            ->with('success', 'Data bioskop berhasil diperbarui.');
    }

    public function toggleActive(Cinema $cinema)
    {
        $cinema->update(['is_active' => !$cinema->is_active]);

        return back()->with(
            'success',
            $cinema->is_active
                ? "{$cinema->name} diaktifkan."
                : "{$cinema->name} dinonaktifkan."
        );
    }

    public function destroy(Cinema $cinema)
    {
        // Hapus logo
        if ($cinema->logo) {
            Storage::disk('public')->delete($cinema->logo);
        }

        $cinema->delete();

        return redirect()
            ->route('admin.cinema.index')
            ->with('success', "Bioskop {$cinema->name} berhasil dihapus.");
    }

    public function storeStudio(Request $request, Cinema $cinema)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:regular,3d,imax,4dx,vip,premiere',
            'rows'       => 'required|integer|min:1|max:26',
            'cols'       => 'required|integer|min:1|max:40',
            'facilities' => 'nullable|array',
            'is_active'  => 'boolean',
            'order'      => 'integer|min:0',
            // Layout config
            'vip_rows'    => 'nullable|string',
            'couple_rows' => 'nullable|string',
            'aisle_after' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $studio = Studio::create([
                'cinema_id'  => $cinema->id,
                'name'       => $validated['name'],
                'type'       => $validated['type'],
                'rows'       => $validated['rows'],
                'cols'       => $validated['cols'],
                'facilities' => $validated['facilities'] ?? [],
                'is_active'  => $validated['is_active'] ?? true,
                'order'      => $validated['order'] ?? 0,
                'total_seats' => 0,
            ]);

            // Parse config
            $vipRows    = $this->parseRowList($validated['vip_rows'] ?? '');
            $coupleRows = $this->parseRowList($validated['couple_rows'] ?? '');
            $aisleAfter = $this->parseIntList($validated['aisle_after'] ?? '');

            // Generate seat layout
            $this->generateSeatLayout($studio, [
                'rows'        => $validated['rows'],
                'cols'        => $validated['cols'],
                'vip_rows'    => $vipRows,
                'couple_rows' => $coupleRows,
                'aisle_after' => $aisleAfter,
                'skip_seats'  => [],
            ]);

            $studio->syncTotalSeats();

            DB::commit();

            return redirect()
                ->route('admin.cinema.show', $cinema)
                ->with('success', "Studio {$studio->name} berhasil ditambahkan dengan {$studio->total_seats} kursi.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Studio creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat studio: ' . $e->getMessage());
        }
    }

    public function toggleStudio(Cinema $cinema, Studio $studio)
    {
        abort_if($studio->cinema_id !== $cinema->id, 403);
        $studio->update(['is_active' => !$studio->is_active]);
        return back()->with(
            'success',
            "Studio {$studio->name} " . ($studio->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.'
        );
    }

    public function destroyStudio(Cinema $cinema, Studio $studio)
    {
        abort_if($studio->cinema_id !== $cinema->id, 403);

        // Cek apakah ada showtime aktif
        if ($studio->showtimes()->whereIn('status', ['open'])->exists()) {
            return back()->with(
                'error',
                'Studio tidak dapat dihapus karena masih memiliki jadwal aktif.'
            );
        }

        DB::beginTransaction();
        try {
            SeatLayout::where('studio_id', $studio->id)->delete();
            $studio->delete();
            DB::commit();

            return back()->with('success', "Studio {$studio->name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus studio.');
        }
    }

    private function generateSeatLayout(Studio $studio, array $config): void
    {
        $rows       = $config['rows'];
        $cols       = $config['cols'];
        $vipRows    = $config['vip_rows'];
        $coupleRows = $config['couple_rows'];
        $aisleAfter = $config['aisle_after'];
        $skipSeats  = $config['skip_seats'];

        $batch = [];

        for ($r = 0; $r < $rows; $r++) {
            $rowLabel = chr(65 + $r);
            for ($c = 1; $c <= $cols; $c++) {
                if (in_array($c, $aisleAfter)) continue;

                $skipCols = $skipSeats[$rowLabel] ?? [];
                $isBlocked = in_array($c, $skipCols);

                $seatType = 'regular';
                if (!$isBlocked) {
                    if (in_array($rowLabel, $vipRows))    $seatType = 'vip';
                    if (in_array($rowLabel, $coupleRows)) $seatType = 'couple';
                }

                $batch[] = [
                    'studio_id'   => $studio->id,
                    'seat_number' => $rowLabel . $c,
                    'row_label'   => $rowLabel,
                    'col_number'  => $c,
                    'seat_type'   => $isBlocked ? 'blocked' : $seatType,
                    'is_active'   => !$isBlocked,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        foreach (array_chunk($batch, 200) as $chunk) {
            SeatLayout::insert($chunk);
        }
    }

    private function parseRowList(string $input): array
    {
        if (empty(trim($input))) return [];
        return array_filter(
            array_map('strtoupper', array_map('trim', explode(',', $input)))
        );
    }

    private function parseIntList(string $input): array
    {
        if (empty(trim($input))) return [];
        return array_filter(
            array_map('intval', array_map('trim', explode(',', $input)))
        );
    }
}
