<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Variabel untuk data umum
    protected $title = 'Users';
    protected $menu = 'users';
    protected $directory = 'admin.users'; // Diubah ke folder view users

    public function index()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mengambil data dari database
        $data['users'] = User::where('role', 'Siswa')->latest()->get();

        // Me-return view beserta data
        return view($this->directory . '.index', $data);
    }

    public function create()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Me-return view beserta data
        return view($this->directory . '.create', $data);
    }

    public function store(Request $request)
    {
        // 1. Validasi data
    $validatedData = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:5',
        'role' => 'required',
        'kelas' => 'nullable|in:X LPS 1,X LPS 2,X DKV 1,X DKV 2,X APHP 1,X APHP 2,X TB 1,X TB 2,X RPL 1,X RPL 2,X RPL 3,XI LPS 1,XI LPS 2,XI DKV 1,XI DKV 2,XI APHP 1,XI APHP 2,XI TB 1,XI TB 2,XI RPL 1,XI RPL 2,XI RPL 3,XII LPS 1,XII LPS 2,XII DKV 1,XII DKV 2,XII APHP 1,XII APHP 2,XII TB 1,XII TB 2,XII RPL 1,XII RPL 2,XII RPL 3'
    ]);

    // 2. Enkripsi password
    $validatedData['password'] = Hash::make($validatedData['password']);

    // 3. Simpan data ke database
    $user = User::create($validatedData);

    // 4. Redirect dengan pesan sukses
    if ($user) {
        return redirect()->route('users.index')->with([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Berhasil Ditambahkan!'
        ]);
    } else {
        return redirect()->route('users.index')->with([
            'status' => 'danger',
            'title' => 'Gagal',
            'message' => 'Data Gagal Ditambahkan!'
        ]);
    }
}

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;
        
        // Mencari data user berdasarkan ID
        $data['user'] = User::findOrFail($id);
        
        // Me-return view beserta data
        return view($this->directory . '.edit', $data);
    }

    public function update(Request $request, string $id)
    {
        // 1. Cari data user berdasarkan ID
        $user = User::findOrFail($id);

    // 2. Validasi data
    $validatedData = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|min:5', // Password boleh kosong
        'role' => 'required',
        'kelas' => 'nullable|in:X LPS 1,X LPS 2,X DKV 1,X DKV 2,X APHP 1,X APHP 2,X TB 1,X TB 2,X RPL 1,X RPL 2,X RPL 3,XI LPS 1,XI LPS 2,XI DKV 1,XI DKV 2,XI APHP 1,XI APHP 2,XI TB 1,XI TB 2,XI RPL 1,XI RPL 2,XI RPL 3,XII LPS 1,XII LPS 2,XII DKV 1,XII DKV 2,XII APHP 1,XII APHP 2,XII TB 1,XII TB 2,XII RPL 1,XII RPL 2,XII RPL 3'
    ]);

    // 3. Menyiapkan data untuk diupdate
    $updateData = [
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'role' => $validatedData['role'],
        'kelas' => $validatedData['kelas'] ?? $user->kelas,
    ];

    // 4. Jika password diisi, enkripsi dan tambahkan ke data update
    if ($request->filled('password')) {
        $updateData['password'] = Hash::make($validatedData['password']);
    }

    // 5. Update data di database
    $updateProcess = $user->update($updateData);

    // 6. Redirect dengan pesan sukses
    if ($updateProcess) {
        return redirect()->route('users.index')->with([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Berhasil Diubah!'
        ]);
    } else {
        return redirect()->route('users.index')->with([
            'status' => 'danger',
            'title' => 'Gagal',
            'message' => 'Data Gagal Diubah!'
        ]);
    }
    }

    public function destroy(string $id)
    {
        // 1. Cari data user berdasarkan ID
        $user = User::findOrFail($id);

    // 2. Lakukan proses delete
    if ($user) {
        $user->delete();

        // 3. Jika berhasil, redirect dengan pesan sukses
        return redirect()->route('users.index')->with([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Berhasil Dihapus!'
        ]);
    } else {
        // 4. Jika gagal, redirect dengan pesan gagal
        return redirect()->route('users.index')->with([
            'status' => 'danger',
            'title' => 'Gagal',
            'message' => 'Data Gagal Dihapus!'
        ]);
    }
    }
}
