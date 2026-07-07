<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('role');

        $users = User::when($filter, fn ($q) => $q->where('role', $filter))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Pengguna/Index', [
            'users'  => $users,
            'filter' => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'role'          => 'required|in:admin,ketua_tim,auditor,auditee,supervisor',
            'nama_instansi' => 'nullable|string|max:255',
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah terdaftar.',
            'role.required'  => 'Role wajib dipilih.',
            'role.in'        => 'Role tidak valid.',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'role'          => $validated['role'],
            'nama_instansi' => $validated['nama_instansi'] ?? null,
            'password'      => Hash::make($password),
        ]);

        activity('pengguna')->causedBy(auth()->user())->performedOn($user)
            ->withProperties(['role' => $user->role, 'email' => $user->email])
            ->log("Tambah pengguna: {$user->name}");

        return back()->with('generatedPassword', $password)
                     ->with('success', "Pengguna {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'role'          => 'required|in:admin,ketua_tim,auditor,auditee,supervisor',
            'nama_instansi' => 'nullable|string|max:255',
            'password'      => 'nullable|string|min:8',
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan pengguna lain.',
            'role.required'  => 'Role wajib dipilih.',
            'password.min'   => 'Password minimal 8 karakter.',
        ]);

        $data = [
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'role'          => $validated['role'],
            'nama_instansi' => $validated['nama_instansi'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $pengguna->update($data);

        activity('pengguna')->causedBy(auth()->user())->performedOn($pengguna)
            ->withProperties(['role' => $pengguna->role, 'email' => $pengguna->email])
            ->log("Edit pengguna: {$pengguna->name}");

        return back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        activity('pengguna')->causedBy(auth()->user())
            ->withProperties(['email' => $pengguna->email, 'role' => $pengguna->role])
            ->log("Hapus pengguna: {$pengguna->name}");

        $pengguna->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
