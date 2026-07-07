<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditPlanAuditor;
use App\Models\BuktiButir;
use App\Models\ButirPenilaian;
use App\Models\PenilaianButir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KuesionerController extends Controller
{
    public function index()
    {
        $plan = AuditPlan::whereHas('auditRequest', fn ($q) => $q->where('auditee_id', auth()->id()))
            ->latest()
            ->first();

        if (! $plan) {
            return Inertia::render('Auditee/Kuesioner', [
                'plan'          => null,
                'butirByBagian' => (object) [],
            ]);
        }

        return redirect()->route('auditee.kuesioner.show', $plan->id);
    }

    public function show(int $planId)
    {
        $plan = AuditPlan::with('auditRequest')
            ->whereHas('auditRequest', fn ($q) => $q->where('auditee_id', auth()->id()))
            ->findOrFail($planId);

        // Pastikan semua 150 butir punya record penilaian (tidak tergantung bagian auditor)
        $this->ensureAllPenilaianExist($planId);

        $penilaian = PenilaianButir::with(['butir', 'buktiButir'])
            ->where('audit_plan_id', $planId)
            ->get()
            ->groupBy('butir_id')
            ->map(fn ($g) => $g->sortBy('id')->first());

        $butirByBagian = $penilaian->values()
            ->groupBy(fn ($p) => $p->butir->bagian)
            ->map(fn ($group) => $group->sortBy('butir.nomor')->values()->map(fn ($p) => [
                'penilaian_id'   => $p->id,
                'butir_id'       => $p->butir_id,
                'bagian'         => $p->butir->bagian,
                'kode'           => $p->butir->kode,
                'nomor'          => $p->butir->nomor,
                'domain'         => $p->butir->domain,
                'judul_butir'    => $p->butir->judul_butir,
                'sumber_acuan'   => $p->butir->sumber_acuan,
                'acuan_edk'      => $p->butir->acuan_edk,
                'acuan_eik'      => $p->butir->acuan_eik,
                'acuan_efk'      => $p->butir->acuan_efk,
                'jawaban_auditee'=> $p->jawaban_auditee,
                'bukti'          => $p->buktiButir->map(fn ($b) => [
                    'id'         => $b->id,
                    'jenis_acuan'=> $b->jenis_acuan,
                    'nama_file'  => $b->nama_file,
                    'path_file'  => $b->path_file,
                ])->values(),
            ])->values());

        return Inertia::render('Auditee/Kuesioner', [
            'plan' => [
                'id'               => $plan->id,
                'instansi'         => $plan->auditRequest->nama_instansi,
                'waktu_mulai'      => optional($plan->waktu_mulai)->format('d M Y'),
                'waktu_selesai'    => optional($plan->waktu_selesai)->format('d M Y'),
                'status_pengisian' => $plan->status_pengisian,
            ],
            'butirByBagian' => $butirByBagian,
        ]);
    }

    public function saveJawaban(Request $request, PenilaianButir $penilaian)
    {
        $this->authorizePlan($penilaian->audit_plan_id);
        // 'nullable' agar string kosong (penghapusan) pun diterima dan disimpan
        $request->validate(['jawaban' => 'nullable|string|max:10000']);
        $penilaian->update(['jawaban_auditee' => $request->jawaban ?? '']);
        return response()->json(['ok' => true]);
    }

    public function uploadBukti(Request $request, PenilaianButir $penilaian)
    {
        $this->authorizePlan($penilaian->audit_plan_id);

        $request->validate([
            'file'        => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240',
            'jenis_acuan' => 'required|in:edk,eik,efk',
        ], [
            'file.mimes' => 'Format file harus PDF, DOCX, PNG, atau JPG.',
            'file.max'   => 'Ukuran file maksimal 10 MB.',
        ]);

        $planId  = $penilaian->audit_plan_id;
        $butirId = $penilaian->butir_id;
        $path    = $request->file('file')->store("bukti/{$planId}/{$butirId}", 'public');

        $bukti = BuktiButir::create([
            'penilaian_id' => $penilaian->id,
            'jenis_acuan'  => $request->jenis_acuan,
            'auditee_id'   => auth()->id(),
            'path_file'    => $path,
            'nama_file'    => $request->file('file')->getClientOriginalName(),
        ]);

        return response()->json([
            'id'          => $bukti->id,
            'jenis_acuan' => $bukti->jenis_acuan,
            'nama_file'   => $bukti->nama_file,
            'path_file'   => $bukti->path_file,
        ]);
    }

    public function deleteBukti(BuktiButir $bukti)
    {
        if ($bukti->auditee_id !== auth()->id()) {
            abort(403, 'Bukan milik Anda.');
        }
        Storage::disk('public')->delete($bukti->path_file);
        $bukti->delete();
        return response()->json(['ok' => true]);
    }

    public function tandaiSelesai(int $planId)
    {
        $plan = AuditPlan::with('auditRequest')
            ->whereHas('auditRequest', fn ($q) => $q->where('auditee_id', auth()->id()))
            ->findOrFail($planId);

        // Validasi server-side: semua butir harus punya jawaban + minimal 1 bukti
        $leadPerButir = PenilaianButir::with('buktiButir')
            ->where('audit_plan_id', $planId)
            ->get()
            ->groupBy('butir_id')
            ->map(fn ($g) => $g->sortBy('id')->first());

        $belum = $leadPerButir->filter(
            fn ($p) => empty(trim($p->jawaban_auditee ?? '')) || $p->buktiButir->isEmpty()
        )->count();

        if ($belum > 0) {
            return back()->with('error',
                "Pengisian belum lengkap. Masih ada {$belum} butir yang belum memiliki tanggapan dan/atau bukti.");
        }

        $plan->update(['status_pengisian' => 'selesai']);

        return redirect()->route('auditee.dashboard')
            ->with('success', 'Pengisian kuesioner berhasil ditandai selesai!');
    }

    private function authorizePlan(int $planId): void
    {
        $owned = AuditPlan::whereHas('auditRequest', fn ($q) => $q->where('auditee_id', auth()->id()))
            ->where('id', $planId)
            ->exists();

        if (! $owned) {
            abort(403, 'Bukan audit Anda.');
        }
    }

    /**
     * Pastikan setiap butir (TK+MK+FK = 150) memiliki minimal satu record penilaian_butir
     * untuk plan ini, sehingga auditee selalu bisa mengisi semua bagian.
     *
     * Butir yang belum tercakup (karena tidak ada auditor di bagian tersebut)
     * dibuatkan record baru menggunakan ketua tim sebagai auditor_id.
     */
    private function ensureAllPenilaianExist(int $planId): void
    {
        $coveredButirIds = PenilaianButir::where('audit_plan_id', $planId)->pluck('butir_id');
        $missingIds      = ButirPenilaian::whereNotIn('id', $coveredButirIds)->pluck('id');

        if ($missingIds->isEmpty()) {
            return;
        }

        // Gunakan ketua tim sebagai pemilik record placeholder
        $ketuaId = AuditPlanAuditor::where('audit_plan_id', $planId)
            ->where('peran', 'ketua')
            ->value('user_id')
            ?? AuditPlanAuditor::where('audit_plan_id', $planId)->value('user_id');

        if (! $ketuaId) {
            return; // Tidak ada auditor terdaftar — lewati
        }

        $now = now();
        PenilaianButir::insert(
            $missingIds->map(fn ($butirId) => [
                'audit_plan_id' => $planId,
                'auditor_id'    => $ketuaId,
                'butir_id'      => $butirId,
                'created_at'    => $now,
                'updated_at'    => $now,
            ])->toArray()
        );
    }
}
