<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditPlanAuditor;
use App\Models\ButirPenilaian;
use App\Models\TemuanAudit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemuanController extends Controller
{
    public function index(Request $request)
    {
        $planId     = (int) $request->query('plan');
        $isKetuaTim = auth()->user()->role === 'ketua_tim';

        // Tanpa ?plan=X → tampilkan daftar plan (Level 1)
        if (! $planId) {
            return $this->showPlanList($isKetuaTim);
        }

        if (! $isKetuaTim) {
            AuditPlanAuditor::where('audit_plan_id', $planId)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $plan = AuditPlan::with('auditRequest')->findOrFail($planId);

        $temuan = TemuanAudit::with(['butir', 'auditor'])
            ->where('audit_plan_id', $planId)
            ->where('is_aktif', true)
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id'                   => $t->id,
                'judul'                => $t->judul ?? '-',
                'deskripsi'            => $t->deskripsi,
                'risiko'               => $t->risiko,
                'rekomendasi'          => $t->rekomendasi,
                'status_tindak_lanjut' => $t->status_tindak_lanjut,
                'butir_kode'           => $t->butir?->kode ?? '-',
                'butir_judul'          => $t->butir?->judul_butir ?? '-',
                'auditor_name'         => $t->auditor?->name ?? '-',
                'auditor_id'           => $t->auditor_id,
                'butir_id'             => $t->butir_id,
                'deadline'             => $t->deadline?->format('Y-m-d'),
                'sumber'               => $t->sumber,
                'jenis_kelemahan'      => $t->jenis_kelemahan ?? [],
                'is_lengkap'           => (bool) $t->is_lengkap,
            ]);

        // Daftar butir penilaian untuk dropdown (hanya butir yang relevan untuk auditor ini)
        $butirList = ButirPenilaian::orderBy('bagian')->orderBy('nomor')->get(['id','kode','judul_butir','bagian']);

        return Inertia::render('Auditor/Temuan', [
            'plan' => [
                'id'      => $plan->id,
                'instansi'=> $plan->auditRequest->nama_instansi ?? '-',
            ],
            'temuan'     => $temuan,
            'butirList'  => $butirList,
            'isKetuaTim' => $isKetuaTim,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_plan_id' => 'required|exists:audit_plans,id',
            'butir_id'      => 'required|exists:butir_penilaian,id',
            'judul'         => 'required|string|max:255',
            'deskripsi'     => 'required|string',
            'risiko'        => 'required|in:tinggi,sedang,rendah',
            'rekomendasi'   => 'required|string',
            'deadline'      => 'sometimes|nullable|date',
        ]);

        $isKetuaTim = auth()->user()->role === 'ketua_tim';
        if (! $isKetuaTim) {
            AuditPlanAuditor::where('audit_plan_id', $validated['audit_plan_id'])
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $temuan = TemuanAudit::create([
            ...$validated,
            'auditor_id'           => auth()->id(),
            'status_tindak_lanjut' => 'proses',
            'sumber'               => 'manual',
            'is_lengkap'           => true,
        ]);

        return response()->json(['ok' => true, 'id' => $temuan->id]);
    }

    public function update(Request $request, TemuanAudit $temuan)
    {
        if ($temuan->auditor_id !== auth()->id() && auth()->user()->role !== 'ketua_tim') {
            abort(403);
        }

        $validated = $request->validate([
            'judul'       => 'required|string|max:255',
            'deskripsi'   => 'required|string',
            'risiko'      => 'required|in:tinggi,sedang,rendah',
            'rekomendasi' => 'required|string',
            'butir_id'    => 'required|exists:butir_penilaian,id',
            'deadline'    => 'sometimes|nullable|date',
            'is_lengkap'  => 'sometimes|boolean',
        ]);

        $temuan->update($validated);

        return response()->json(['ok' => true]);
    }

    public function destroy(TemuanAudit $temuan)
    {
        if ($temuan->auditor_id !== auth()->id() && auth()->user()->role !== 'ketua_tim') {
            abort(403);
        }

        $temuan->delete();

        return response()->json(['ok' => true]);
    }

    // Level 1 — daftar plan pilihan sebelum masuk ke temuan spesifik
    private function showPlanList(bool $isKetuaTim): \Inertia\Response
    {
        if ($isKetuaTim) {
            // Ketua tim melihat SEMUA plan
            $plans = AuditPlan::with(['auditRequest', 'temuanAudit' => fn ($q) => $q->where('is_aktif', true)])
                ->latest()
                ->get()
                ->map(fn ($p) => [
                    'id'           => $p->id,
                    'instansi'     => $p->auditRequest->nama_instansi ?? '-',
                    'url_target'   => $p->auditRequest->url_target ?? '-',
                    'waktu_mulai'  => optional($p->waktu_mulai)->format('d M Y'),
                    'total_temuan' => $p->temuanAudit->count(),
                    'base_url'     => '/ketua-tim/temuan',
                ]);

            return Inertia::render('Shared/TemuanIndex', ['plans' => $plans, 'isKetuaTim' => true]);
        }

        // Auditor hanya melihat plan yang ditugaskan
        $plans = AuditPlanAuditor::with(['auditPlan.auditRequest', 'auditPlan.temuanAudit' => fn ($q) => $q->where('is_aktif', true)])
            ->where('user_id', auth()->id())
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->auditPlan->id,
                'instansi'     => $a->auditPlan->auditRequest->nama_instansi ?? '-',
                'url_target'   => $a->auditPlan->auditRequest->url_target ?? '-',
                'waktu_mulai'  => optional($a->auditPlan->waktu_mulai)->format('d M Y'),
                'total_temuan' => $a->auditPlan->temuanAudit->count(),
                'base_url'     => '/auditor/temuan',
            ]);

        return Inertia::render('Shared/TemuanIndex', ['plans' => $plans, 'isKetuaTim' => false]);
    }
}
