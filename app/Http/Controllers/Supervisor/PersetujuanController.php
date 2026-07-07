<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AuditResult;
use App\Models\LhakApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PersetujuanController extends Controller
{
    public function index()
    {
        $approvals = LhakApproval::with(['auditPlan.auditRequest', 'submitter', 'reviewer'])
            ->orderByRaw("FIELD(status, 'pending', 'ditolak', 'disetujui')")
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->id,
                'instansi'     => $a->auditPlan->auditRequest->nama_instansi ?? '-',
                'aplikasi'     => $a->auditPlan->auditRequest->url_target ?? '-',
                'submitted_by' => $a->submitter?->name ?? '-',
                'reviewed_by'  => $a->reviewer?->name,
                'status'       => $a->status,
                'submitted_at' => $a->submitted_at?->format('d M Y H:i'),
                'reviewed_at'  => $a->reviewed_at?->format('d M Y H:i'),
            ]);

        return Inertia::render('Supervisor/PersetujuanIndex', [
            'approvals' => $approvals,
        ]);
    }

    public function show(LhakApproval $approval)
    {
        $approval->load(['auditPlan.auditRequest', 'submitter', 'reviewer']);

        $fileUrl    = Storage::disk('public')->exists($approval->file_lhak)
            ? Storage::disk('public')->url($approval->file_lhak)
            : null;
        $fileTteUrl = $approval->file_lhak_tte && Storage::disk('public')->exists($approval->file_lhak_tte)
            ? Storage::disk('public')->url($approval->file_lhak_tte)
            : null;

        return Inertia::render('Supervisor/PersetujuanShow', [
            'approval' => [
                'id'           => $approval->id,
                'instansi'     => $approval->auditPlan->auditRequest->nama_instansi ?? '-',
                'aplikasi'     => $approval->auditPlan->auditRequest->url_target ?? '-',
                'submitted_by' => $approval->submitter?->name ?? '-',
                'reviewed_by'  => $approval->reviewer?->name,
                'status'       => $approval->status,
                'catatan'      => $approval->catatan,
                'submitted_at' => $approval->submitted_at?->format('d M Y H:i'),
                'reviewed_at'  => $approval->reviewed_at?->format('d M Y H:i'),
                'file_url'     => $fileUrl,
                'file_tte_url' => $fileTteUrl,
            ],
        ]);
    }

    public function setujui(Request $request, LhakApproval $approval)
    {
        abort_if($approval->status !== 'pending', 422, 'Persetujuan sudah diproses.');

        $request->validate([
            'file_tte' => 'required|file|mimes:pdf|max:10240',
        ], [
            'file_tte.required' => 'File LHAK ber-TTE wajib diunggah.',
            'file_tte.mimes'    => 'File harus berformat PDF.',
            'file_tte.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $path = $request->file('file_tte')->store('lhak/tte', 'public');

        $approval->update([
            'reviewed_by'   => auth()->id(),
            'status'        => 'disetujui',
            'file_lhak_tte' => $path,
            'reviewed_at'   => now(),
        ]);

        // Timpa path_lhak di audit_results agar auditee mengunduh LHAK ber-TTE
        AuditResult::where('audit_plan_id', $approval->audit_plan_id)
            ->update(['path_lhak' => $path]);

        activity('lhak_approval')->causedBy(auth()->user())->performedOn($approval)
            ->log("Setujui LHAK: {$approval->auditPlan->auditRequest->nama_instansi}");

        return back()->with('success', 'LHAK berhasil disetujui.');
    }

    public function tolak(Request $request, LhakApproval $approval)
    {
        abort_if($approval->status !== 'pending', 422, 'Persetujuan sudah diproses.');

        $request->validate([
            'catatan' => 'required|string|min:10|max:2000',
        ], [
            'catatan.required' => 'Catatan penolakan wajib diisi.',
            'catatan.min'      => 'Catatan minimal 10 karakter.',
        ]);

        $approval->update([
            'reviewed_by' => auth()->id(),
            'status'      => 'ditolak',
            'catatan'     => $request->catatan,
            'reviewed_at' => now(),
        ]);

        activity('lhak_approval')->causedBy(auth()->user())->performedOn($approval)
            ->log("Tolak LHAK: {$approval->auditPlan->auditRequest->nama_instansi}");

        return back()->with('success', 'LHAK berhasil ditolak.');
    }

    public function download(LhakApproval $approval)
    {
        $path = $approval->file_lhak_tte ?? $approval->file_lhak;
        abort_if(! Storage::disk('public')->exists($path), 404, 'File tidak ditemukan.');

        return Storage::disk('public')->download($path);
    }
}
