<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditPlanAuditor;
use App\Models\AuditRequest;
use App\Models\ButirPenilaian;
use App\Models\PenilaianButir;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditPlanController extends Controller
{
    public function index()
    {
        // Ketua tim melihat SEMUA audit plan di sistem (kebijakan supervisor umum)
        $plans = AuditPlan::with(['auditRequest', 'auditors.user'])
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id'            => $p->id,
                'instansi'      => $p->auditRequest->nama_instansi ?? '-',
                'url_target'    => $p->auditRequest->url_target ?? '-',
                'waktu_mulai'   => optional($p->waktu_mulai)->format('d M Y'),
                'waktu_selesai' => optional($p->waktu_selesai)->format('d M Y'),
                'auditors'      => $p->auditors->map(fn ($a) => [
                    'name'   => $a->user->name ?? '-',
                    'peran'  => $a->peran,
                    'bagian' => $a->bagian,
                ]),
            ]);

        // Pengajuan disetujui yang BELUM punya plan → tampilkan tombol "Buat Plan"
        $pengajuanDisetujui = AuditRequest::where('status', 'disetujui')
            ->whereDoesntHave('auditPlans')
            ->get()
            ->map(fn ($r) => [
                'id'            => $r->id,
                'nama_instansi' => $r->nama_instansi,
                'url_target'    => $r->url_target,
                'plan_id'       => null,
            ]);

        // Pengajuan disetujui yang SUDAH punya plan → tampilkan tombol "Lihat Plan"
        $pengajuanDenganPlan = AuditRequest::where('status', 'disetujui')
            ->whereHas('auditPlans')
            ->with('auditPlans')
            ->get()
            ->map(fn ($r) => [
                'id'            => $r->id,
                'nama_instansi' => $r->nama_instansi,
                'url_target'    => $r->url_target,
                'plan_id'       => $r->auditPlans->first()?->id,
            ]);

        return Inertia::render('KetuaTim/AuditPlan/Index', compact('plans', 'pengajuanDisetujui', 'pengajuanDenganPlan'));
    }

    public function create(AuditRequest $pengajuan)
    {
        if ($pengajuan->status !== 'disetujui') {
            return redirect()->route('ketua_tim.audit_plan.index')
                ->with('error', 'Hanya pengajuan berstatus disetujui yang dapat dibuatkan audit plan.');
        }

        if (AuditPlan::where('audit_request_id', $pengajuan->id)->exists()) {
            return redirect()->route('ketua_tim.audit_plan.index')
                ->with('error', 'Audit plan untuk pengajuan ini sudah dibuat sebelumnya.');
        }

        $auditors = User::whereIn('role', ['auditor', 'ketua_tim'])
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'   => $u->id,
                'name' => $u->name,
                'role' => $u->role,
            ]);

        return Inertia::render('KetuaTim/AuditPlan/Create', [
            'pengajuan' => [
                'id'            => $pengajuan->id,
                'nama_instansi' => $pengajuan->nama_instansi,
                'url_target'    => $pengajuan->url_target,
            ],
            'auditors' => $auditors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_request_id' => 'required|exists:audit_requests,id',
            'waktu_mulai'      => 'required|date',
            'waktu_selesai'    => 'required|date|after:waktu_mulai',
            'auditors'         => 'required|array|min:1',
            'auditors.*.user_id' => 'required|exists:users,id',
            'auditors.*.peran'   => 'required|in:ketua,anggota',
            'auditors.*.bagian'  => 'required|in:semua,tk_mk,fk',
        ], [
            'waktu_mulai.required'    => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required'  => 'Waktu selesai wajib diisi.',
            'waktu_selesai.after'     => 'Waktu selesai harus setelah waktu mulai.',
            'auditors.required'       => 'Minimal 1 auditor harus ditambahkan.',
            'auditors.*.user_id.required' => 'Auditor wajib dipilih.',
            'auditors.*.peran.required'   => 'Peran auditor wajib dipilih.',
            'auditors.*.bagian.required'  => 'Bagian penilaian wajib dipilih.',
        ]);

        // Cegah duplikat audit plan untuk pengajuan yang sama
        $existing = AuditPlan::where('audit_request_id', $validated['audit_request_id'])->first();
        if ($existing) {
            return redirect()->route('ketua_tim.audit_plan.index')
                ->with('error', 'Audit plan untuk pengajuan ini sudah dibuat sebelumnya.');
        }

        $plan = AuditPlan::create([
            'audit_request_id' => $validated['audit_request_id'],
            'waktu_mulai'      => $validated['waktu_mulai'],
            'waktu_selesai'    => $validated['waktu_selesai'],
        ]);

        // Otomatis tambahkan ketua tim yang membuat sebagai penanggung jawab
        AuditPlanAuditor::create([
            'audit_plan_id' => $plan->id,
            'user_id'       => auth()->id(),
            'peran'         => 'ketua',
            'bagian'        => 'semua',
        ]);

        $bagianMap = [
            'semua' => ['tk', 'mk', 'fk'],
            'tk_mk' => ['tk', 'mk'],
            'fk'    => ['fk'],
        ];

        foreach ($validated['auditors'] as $auditorData) {
            AuditPlanAuditor::create([
                'audit_plan_id' => $plan->id,
                'user_id'       => $auditorData['user_id'],
                'peran'         => $auditorData['peran'],
                'bagian'        => $auditorData['bagian'],
            ]);

            $bagianFilter = $bagianMap[$auditorData['bagian']];
            $butirIds = ButirPenilaian::whereIn('bagian', $bagianFilter)->pluck('id');

            $rows = $butirIds->map(fn ($butirId) => [
                'audit_plan_id' => $plan->id,
                'auditor_id'    => $auditorData['user_id'],
                'butir_id'      => $butirId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ])->toArray();

            PenilaianButir::insert($rows);
        }

        // Pastikan semua butir (TK+MK+FK) tercover — butir yang tidak ada auditor
        // di bagiannya dibuatkan record menggunakan ketua tim, agar auditee bisa
        // mengisi seluruh kuesioner tanpa terkendala bagian auditor.
        $ketuaUserId     = collect($validated['auditors'])
            ->firstWhere('peran', 'ketua')['user_id'] ?? auth()->id();
        $coveredButirIds = PenilaianButir::where('audit_plan_id', $plan->id)->pluck('butir_id');
        $missingIds      = ButirPenilaian::whereNotIn('id', $coveredButirIds)->pluck('id');

        if ($missingIds->isNotEmpty()) {
            $now = now();
            PenilaianButir::insert(
                $missingIds->map(fn ($butirId) => [
                    'audit_plan_id' => $plan->id,
                    'auditor_id'    => $ketuaUserId,
                    'butir_id'      => $butirId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ])->toArray()
            );
        }

        return redirect()->route('ketua_tim.audit_plan.index')
            ->with('success', 'Audit plan berhasil dibuat dan penilaian butir telah disiapkan.');
    }
}
