<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuan = AuditRequest::where('auditee_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'            => $r->id,
                'nama_instansi' => $r->nama_instansi,
                'url_target'    => $r->url_target,
                'status'        => $r->status,
                'alasan_tolak'  => $r->alasan_tolak,
                'created_at'    => $r->created_at->format('d M Y'),
            ]);

        return Inertia::render('Auditee/Pengajuan/Index', compact('pengajuan'));
    }

    public function create()
    {
        return Inertia::render('Auditee/Pengajuan/Create', [
            'namaInstansi' => auth()->user()->nama_instansi ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'url_target'    => 'required|url|max:255',
            'daftar_tim'    => 'nullable|string|max:500',
            'nda'           => 'nullable|file|mimes:pdf|max:5120',
        ], [
            'nama_instansi.required' => 'Nama instansi wajib diisi.',
            'url_target.required'    => 'URL target wajib diisi.',
            'url_target.url'         => 'Format URL tidak valid. Contoh: https://app.instansi.go.id',
            'nda.mimes'              => 'Dokumen NDA harus berformat PDF.',
            'nda.max'                => 'Ukuran file NDA maksimal 5 MB.',
        ]);

        $pathNda = null;
        if ($request->hasFile('nda')) {
            $pathNda = $request->file('nda')->store('nda', 'public');
        }

        AuditRequest::create([
            'auditee_id'    => auth()->id(),
            'nama_instansi' => $validated['nama_instansi'],
            'url_target'    => $validated['url_target'],
            'daftar_tim'    => $validated['daftar_tim'] ?? null,
            'path_nda'      => $pathNda,
            'status'        => 'menunggu',
        ]);

        return redirect()->route('auditee.pengajuan.index')
            ->with('success', 'Pengajuan audit berhasil dikirim dan sedang menunggu review.');
    }

    public function cancel(AuditRequest $pengajuan)
    {
        if ($pengajuan->auditee_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak membatalkan pengajuan ini.');
        }

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $pengajuan->delete();

        return back()->with('success', 'Pengajuan audit berhasil dibatalkan.');
    }
}
