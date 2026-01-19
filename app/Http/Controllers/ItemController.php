<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    protected $title = 'Item';
    protected $menu = 'item';
    protected $directory = 'admin.item';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with('user')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        $data['items'] = $items;
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;
        $data['total_items'] = Item::count();
        $data['available_items'] = Item::available()->count();
        $data['damaged_items'] = Item::hasDamage()->count();
        $data['fully_damaged'] = Item::fullyDamaged()->count();

        return view($this->directory . '.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;
        $data['users'] = User::all();

        return view($this->directory . '.create', $data);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'unique_code' => 'nullable|unique:items',
            // condition may be set automatically based on damaged_count; allow nullable on create
            'condition' => 'nullable',
            'user_id' => 'required|exists:users,id',
            'stock' => 'required|integer|min:1',
            'damaged_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validasi tambahan untuk jumlah rusak tidak melebihi stok
        if (isset($validatedData['damaged_count']) && $validatedData['damaged_count'] > $validatedData['stock']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['damaged_count' => 'Jumlah barang rusak tidak boleh melebihi total stok']);
        }

        // Jika damaged_count tidak diisi, set ke 0
        if (!isset($validatedData['damaged_count'])) {
            $validatedData['damaged_count'] = 0;
        }

        // Upload foto jika ada
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('photos'), $imageName);
            $validatedData['photo'] = $imageName;
        }

        // Update kondisi berdasarkan jumlah rusak jika tidak diisi
        if (!isset($validatedData['condition']) && $validatedData['damaged_count'] > 0) {
            if ($validatedData['damaged_count'] == $validatedData['stock']) {
                $validatedData['condition'] = 'Rusak';
            } else {
                $validatedData['condition'] = 'Baik';
            }
        }

        $item = Item::create($validatedData);

        // Jika ada barang rusak, buat log atau catatan khusus
        if ($item->damaged_count > 0) {
            // Bisa tambahkan log ke tabel damaged_items_log atau sejenisnya
            $this->logDamagedItem($item, 'tambah', $item->damaged_count);
        }

        if ($item) {
            return redirect()->route('item.index')->with([
                'status'  => 'success',
                'title'   => 'Berhasil',
                'message' => 'Data Berhasil Ditambahkan!'
            ]);
        }

        return redirect()->route('item.index')->with([
            'status'  => 'danger',
            'title'   => 'Gagal',
            'message' => 'Data Gagal Ditambahkan!'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;
        $data['item'] = $item;
        $data['users'] = User::all();

        return view($this->directory . '.edit', $data);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Item $item)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'unique_code' => 'nullable|unique:items,unique_code,' . $item->id,
            'condition' => 'required',
            'user_id' => 'required|exists:users,id',
            'stock' => 'required|integer|min:1',
            'damaged_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validasi jumlah rusak tidak melebihi stok
        if (isset($validatedData['damaged_count']) && $validatedData['damaged_count'] > $validatedData['stock']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['damaged_count' => 'Jumlah barang rusak tidak boleh melebihi total stok']);
        }

        // Simpan jumlah rusak sebelumnya untuk log
        $oldDamagedCount = $item->damaged_count;
        $newDamagedCount = $validatedData['damaged_count'] ?? 0;

        // Upload foto baru jika ada
        if ($request->hasFile('photo')) {
            if ($item->photo && File::exists(public_path('photos/' . $item->photo))) {
                File::delete(public_path('photos/' . $item->photo));
            }

            $image = $request->file('photo');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('photos'), $imageName);
            $validatedData['photo'] = $imageName;
        }

        $updateProcess = $item->update($validatedData);

        // Log perubahan jumlah rusak
        if ($oldDamagedCount != $newDamagedCount) {
            $this->logDamagedItemChange($item, $oldDamagedCount, $newDamagedCount);
        }

        if ($updateProcess) {
            return redirect()->route('item.index')->with([
                'status'  => 'success',
                'title'   => 'Berhasil',
                'message' => 'Data Berhasil Diubah!'
            ]);
        }

        return redirect()->route('item.index')->with([
            'status'  => 'danger',
            'title'   => 'Gagal',
            'message' => 'Data Gagal Diubah!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        // Hapus foto jika ada
        if ($item->photo && File::exists(public_path('photos/' . $item->photo))) {
            File::delete(public_path('photos/' . $item->photo));
        }

        $deleteProcess = $item->delete();

        if ($deleteProcess) {
            return redirect()->route('item.index')->with([
                'status'  => 'success',
                'title'   => 'Berhasil',
                'message' => 'Data Berhasil Dihapus!'
            ]);
        }

        return redirect()->route('item.index')->with([
            'status'  => 'danger',
            'title'   => 'Gagal',
            'message' => 'Data Gagal Dihapus!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;
        $data['item'] = $item;

        return view($this->directory . '.show', $data);
    }

    /**
     * Menampilkan halaman barang rusak
     */
    public function rusak()
    {
        $items = Item::hasDamage()
                    ->with('user')
                    ->orderBy('damaged_count', 'desc')
                    ->get();

        return view($this->directory . '.rusak', [
            'title' => $this->title,
            'menu'  => $this->menu,
            'items' => $items,
            'total_damaged_items' => Item::hasDamage()->sum('damaged_count'),
            'total_fully_damaged' => Item::fullyDamaged()->count(),
        ]);
    }

    /**
     * Menampilkan halaman perbaikan barang
     */
    public function perbaikan()
    {
        $items = Item::hasDamage()
                    ->where('damaged_count', '>', 0)
                    ->with('user')
                    ->orderBy('damaged_count', 'desc')
                    ->get();

        return view($this->directory . '.perbaikan', [
            'title' => $this->title,
            'menu'  => $this->menu,
            'items' => $items,
        ]);
    }

    /**
     * Proses perbaikan barang (mengurangi jumlah rusak)
     */
    public function repair(Request $request, Item $item)
    {
        $request->validate([
            'repair_count' => 'required|integer|min:1|max:' . $item->damaged_count,
            'repair_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $oldCount = $item->damaged_count;
            $repairCount = $request->repair_count;
            
            $item->repairItem($repairCount);
            
            // Simpan catatan perbaikan
            if ($request->repair_notes) {
                $item->notes = ($item->notes ? $item->notes . "\n" : '') . 
                             date('Y-m-d H:i:s') . ' - Perbaikan: ' . $repairCount . ' unit. ' . 
                             $request->repair_notes;
                $item->save();
            }

            // Log perbaikan
            $this->logDamagedItem($item, 'perbaikan', $repairCount, $request->repair_notes);

            DB::commit();

            return redirect()->route('item.rusak')->with([
                'status'  => 'success',
                'title'   => 'Berhasil',
                'message' => $repairCount . ' unit barang berhasil diperbaiki!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with([
                'status'  => 'danger',
                'title'   => 'Gagal',
                'message' => 'Gagal memperbaiki barang: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Menandai barang sebagai rusak (menambah jumlah rusak)
     */
    public function markAsDamaged(Request $request, Item $item)
    {
        $request->validate([
            'damage_count' => 'required|integer|min:1|max:' . ($item->stock - $item->damaged_count),
            'damage_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $oldCount = $item->damaged_count;
            $damageCount = $request->damage_count;
            
            $item->addDamagedItem($damageCount);
            
            // Simpan catatan kerusakan
            if ($request->damage_notes) {
                $item->notes = ($item->notes ? $item->notes . "\n" : '') . 
                             date('Y-m-d H:i:s') . ' - Kerusakan: ' . $damageCount . ' unit. ' . 
                             $request->damage_notes;
                $item->save();
            }

            // Log kerusakan
            $this->logDamagedItem($item, 'kerusakan', $damageCount, $request->damage_notes);

            DB::commit();

            return redirect()->route('item.index')->with([
                'status'  => 'success',
                'title'   => 'Berhasil',
                'message' => $damageCount . ' unit barang ditandai sebagai rusak!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with([
                'status'  => 'danger',
                'title'   => 'Gagal',
                'message' => 'Gagal menandai barang rusak: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Log untuk barang rusak
     */
    private function logDamagedItem(Item $item, $action, $count, $notes = null)
    {
        // Anda bisa membuat tabel damaged_items_log untuk mencatat riwayat
        // atau menggunakan sistem logging yang sudah ada
        
        // Contoh sederhana: simpan di notes item
        $logMessage = date('Y-m-d H:i:s') . " - $action: $count unit";
        if ($notes) {
            $logMessage .= " - $notes";
        }
        
        // Tambahkan ke notes item
        $currentNotes = $item->notes ?? '';
        $item->notes = $currentNotes . ($currentNotes ? "\n" : '') . $logMessage;
        $item->save();
    }

    /**
     * Log perubahan jumlah rusak
     */
    private function logDamagedItemChange(Item $item, $oldCount, $newCount)
    {
        $change = $newCount - $oldCount;
        
        if ($change > 0) {
            $action = 'Penambahan barang rusak';
        } elseif ($change < 0) {
            $action = 'Pengurangan barang rusak (perbaikan)';
            $change = abs($change);
        } else {
            return;
        }
        
        $this->logDamagedItem($item, $action, $change, 
            "Dari $oldCount menjadi $newCount unit");
    }

    /**
     * Get statistics dashboard
     */
    public function statistics()
    {
        $totalItems = Item::sum('stock');
        $totalDamaged = Item::sum('damaged_count');
        $totalAvailable = $totalItems - $totalDamaged;
        
        $damagedPercentage = $totalItems > 0 ? ($totalDamaged / $totalItems * 100) : 0;
        
        $mostDamagedItems = Item::hasDamage()
                                ->orderBy('damaged_count', 'desc')
                                ->take(5)
                                ->get();
        
        $recentDamages = Item::where('damaged_count', '>', 0)
                            ->orderBy('updated_at', 'desc')
                            ->take(10)
                            ->get();

        return view($this->directory . '.statistics', [
            'title' => 'Statistik Barang',
            'menu' => $this->menu,
            'totalItems' => $totalItems,
            'totalDamaged' => $totalDamaged,
            'totalAvailable' => $totalAvailable,
            'damagedPercentage' => round($damagedPercentage, 2),
            'mostDamagedItems' => $mostDamagedItems,
            'recentDamages' => $recentDamages,
        ]);
    }
}