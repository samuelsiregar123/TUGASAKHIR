<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\AuditRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuan = AuditRequest::with('auditee')
            ->orderByRaw("FIELD(status,'menunggu','disetujui','ditolak')")
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'            => $r->id,
                'nama_instansi' => $r->nama_instansi,
                'url_target'    => $r->url_target,
                'daftar_tim'    => $r->daftar_tim,
                'path_nda'      => $r->path_nda,
                'status'        => $r->status,
                'alasan_tolak'  => $r->alasan_tolak,
                'created_at'    => $r->created_at->format('d M Y'),
                'auditee_name'  => $r->auditee->name ?? '-',
            ]);

        return Inertia::render('KetuaTim/Pengajuan/Index', compact('pengajuan'));
    }

    public function setujui(AuditRequest $pengajuan)
    {
        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        }

        $pengajuan->update(['status' => 'disetujui']);

        activity('pengajuan')->causedBy(auth()->user())->performedOn($pengajuan)
            ->log("Setujui pengajuan: {$pengajuan->nama_instansi}");

        return back()->with('success', "Pengajuan {$pengajuan->nama_instansi} berhasil disetujui.");
    }

    public function tolak(Request $request, AuditRequest $pengajuan)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:500',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        }

        $pengajuan->update([
            'status'       => 'ditolak',
            'alasan_tolak' => $request->alasan_tolak,
        ]);

        activity('pengajuan')->causedBy(auth()->user())->performedOn($pengajuan)
            ->withProperties(['alasan' => $request->alasan_tolak])
            ->log("Tolak pengajuan: {$pengajuan->nama_instansi}");

        return back()->with('success', "Pengajuan {$pengajuan->nama_instansi} ditolak.");
    }
}
