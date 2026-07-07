<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auditee;
use App\Http\Controllers\KetuaTim;
use App\Http\Controllers\Auditor;
use App\Http\Controllers\Supervisor;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    /* ── Dashboard redirect berdasar role ── */
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin'      => redirect()->route('admin.dashboard'),
            'ketua_tim'  => redirect()->route('ketua_tim.dashboard'),
            'auditor'    => redirect()->route('auditor.dashboard'),
            'supervisor' => redirect()->route('supervisor.dashboard'),
            default      => redirect()->route('auditee.dashboard'),
        };
    })->name('dashboard');

    /* ═══════════════ ADMIN ═══════════════ */
    Route::prefix('admin')
        ->middleware('role:admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/pengguna',                  [Admin\PenggunaController::class, 'index'])->name('pengguna.index');
            Route::post('/pengguna',                 [Admin\PenggunaController::class, 'store'])->name('pengguna.store');
            Route::put('/pengguna/{pengguna}',       [Admin\PenggunaController::class, 'update'])->name('pengguna.update');
            Route::delete('/pengguna/{pengguna}',    [Admin\PenggunaController::class, 'destroy'])->name('pengguna.destroy');

            Route::get('/audit-log',                 [Admin\AuditLogController::class, 'index'])->name('audit_log');
        });

    /* ═══════════════ AUDITEE ═══════════════ */
    Route::prefix('auditee')
        ->middleware('role:auditee')
        ->name('auditee.')
        ->group(function () {
            Route::get('/dashboard',                           [Auditee\DashboardController::class,  'index'])->name('dashboard');
            Route::get('/pengajuan',                           [Auditee\PengajuanController::class,  'index'])->name('pengajuan.index');
            Route::get('/pengajuan/create',                    [Auditee\PengajuanController::class,  'create'])->name('pengajuan.create');
            Route::post('/pengajuan',                          [Auditee\PengajuanController::class,  'store'])->name('pengajuan.store');
            Route::delete('/pengajuan/{pengajuan}/cancel',     [Auditee\PengajuanController::class,  'cancel'])->name('pengajuan.cancel');

            Route::get('/kuesioner',                                [Auditee\KuesionerController::class, 'index'])->name('kuesioner');
            Route::get('/kuesioner/{plan}',                         [Auditee\KuesionerController::class, 'show'])->name('kuesioner.show');
            Route::put('/penilaian/{penilaian}/jawaban',            [Auditee\KuesionerController::class, 'saveJawaban'])->name('penilaian.jawaban');
            Route::post('/penilaian/{penilaian}/bukti',             [Auditee\KuesionerController::class, 'uploadBukti'])->name('penilaian.bukti');
            Route::delete('/bukti/{bukti}',                         [Auditee\KuesionerController::class, 'deleteBukti'])->name('bukti.destroy');
            Route::post('/kuesioner/{plan}/tandai-selesai',         [Auditee\KuesionerController::class, 'tandaiSelesai'])->name('kuesioner.tandai_selesai');

            Route::get('/lhak',                                     [Auditee\LhakController::class, 'index'])->name('lhak');
            Route::get('/lhak/{plan}/download',                     [Auditee\LhakController::class, 'download'])->name('lhak.download');

            Route::get('/tindak-lanjut',                            [Auditee\TindakLanjutController::class, 'index'])->name('tindak_lanjut');
            Route::get('/tindak-lanjut/{plan}',                     [Auditee\TindakLanjutController::class, 'show'])->name('tindak_lanjut.show');
            Route::post('/tindak-lanjut/{temuan}/kirim',            [Auditee\TindakLanjutController::class, 'kirimPesan'])->name('tindak_lanjut.kirim');
            Route::get('/tindak-lanjut/{temuan}/pesan',             [Auditee\TindakLanjutController::class, 'pesanTerbaru'])->name('tindak_lanjut.pesan');
            Route::post('/tindak-lanjut/{temuan}/deadline',         [Auditee\TindakLanjutController::class, 'setDeadline'])->name('tindak_lanjut.deadline');
        });

    /* ═══════════════ KETUA TIM ═══════════════ */
    Route::prefix('ketua-tim')
        ->middleware('role:ketua_tim')
        ->name('ketua_tim.')
        ->group(function () {
            Route::get('/dashboard',                                [KetuaTim\DashboardController::class,  'index'])->name('dashboard');

            Route::get('/pengajuan',                               [KetuaTim\PengajuanController::class,  'index'])->name('pengajuan.index');
            Route::post('/pengajuan/{pengajuan}/setujui',          [KetuaTim\PengajuanController::class,  'setujui'])->name('pengajuan.setujui');
            Route::post('/pengajuan/{pengajuan}/tolak',            [KetuaTim\PengajuanController::class,  'tolak'])->name('pengajuan.tolak');

            Route::get('/audit-plan',                              [KetuaTim\AuditPlanController::class,  'index'])->name('audit_plan.index');
            Route::get('/audit-plan/create/{pengajuan}',           [KetuaTim\AuditPlanController::class,  'create'])->name('audit_plan.create');
            Route::post('/audit-plan',                             [KetuaTim\AuditPlanController::class,  'store'])->name('audit_plan.store');

            Route::get('/penilaian', [KetuaTim\PenilaianController::class, 'index'])->name('penilaian');

            Route::get('/temuan',                                   [Auditor\TemuanController::class, 'index'])->name('temuan');
            Route::post('/temuan',                                  [Auditor\TemuanController::class, 'store'])->name('temuan.store');
            Route::put('/temuan/{temuan}',                          [Auditor\TemuanController::class, 'update'])->name('temuan.update');
            Route::delete('/temuan/{temuan}',                       [Auditor\TemuanController::class, 'destroy'])->name('temuan.destroy');

            Route::get('/konklusi-lhak',                            [KetuaTim\KonklusiLhakController::class, 'index'])->name('konklusi_lhak');
            Route::get('/konklusi-lhak/{plan}',                     [KetuaTim\KonklusiLhakController::class, 'show'])->name('konklusi_lhak.show');
            Route::post('/konklusi-lhak/{plan}/hitung',             [KetuaTim\KonklusiLhakController::class, 'hitung'])->name('konklusi_lhak.hitung');
            Route::post('/konklusi-lhak/{plan}/generate',           [KetuaTim\KonklusiLhakController::class, 'generate'])->name('konklusi_lhak.generate');
            Route::post('/konklusi-lhak/{plan}/ajukan',             [KetuaTim\KonklusiLhakController::class, 'ajukan'])->name('konklusi_lhak.ajukan');
            Route::get('/konklusi-lhak/{plan}/download',            [KetuaTim\KonklusiLhakController::class, 'download'])->name('konklusi_lhak.download');

            Route::get('/tindak-lanjut',                            [KetuaTim\TindakLanjutController::class, 'index'])->name('tindak_lanjut');
            Route::get('/tindak-lanjut/{plan}',                     [KetuaTim\TindakLanjutController::class, 'show'])->name('tindak_lanjut.show');
            Route::post('/tindak-lanjut/{temuan}/kirim',            [KetuaTim\TindakLanjutController::class, 'kirimPesan'])->name('tindak_lanjut.kirim');
            Route::get('/tindak-lanjut/{temuan}/pesan',             [KetuaTim\TindakLanjutController::class, 'pesanTerbaru'])->name('tindak_lanjut.pesan');
            Route::post('/tindak-lanjut/{temuan}/selesai',          [KetuaTim\TindakLanjutController::class, 'tandaiSelesai'])->name('tindak_lanjut.selesai');
        });

    /* ═══════════════ AUDITOR ═══════════════ */
    Route::prefix('auditor')
        ->middleware('role:auditor,ketua_tim')
        ->name('auditor.')
        ->group(function () {
            Route::get('/dashboard',  [Auditor\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/penilaian',              [Auditor\PenilaianController::class, 'index'])->name('penilaian');
            Route::put('/penilaian/{penilaian}',  [Auditor\PenilaianController::class, 'update'])->name('penilaian.update');
            Route::post('/penilaian/{plan}/validasi-konklusi', [Auditor\PenilaianController::class, 'validasiKonklusi'])->name('penilaian.validasi_konklusi');

            Route::get('/pemindaian',                            [Auditor\ScanController::class, 'index'])->name('pemindaian');
            Route::get('/pemindaian/{plan}',                     [Auditor\ScanController::class, 'show'])->name('pemindaian.show');
            Route::post('/pemindaian/{plan}/scan/start',         [Auditor\ScanController::class, 'start'])->name('scan.start');
            Route::get('/pemindaian/{plan}/scan/status',         [Auditor\ScanController::class, 'status'])->name('scan.status');
            Route::get('/scan/{scan}/result',                    [Auditor\ScanController::class, 'result'])->name('scan.result');
            Route::post('/scan/{scan}/tag-bukti',                [Auditor\ScanController::class, 'tagBukti'])->name('scan.tag_bukti');
            Route::post('/scan/{scan}/rerun',                    [Auditor\ScanController::class, 'rerun'])->name('scan.rerun');
            Route::post('/scan/{scan}/cancel',                   [Auditor\ScanController::class, 'cancel'])->name('scan.cancel');
            Route::post('/pemindaian/{plan}/cancel-all',         [Auditor\ScanController::class, 'cancelAll'])->name('scan.cancel_all');

            Route::get('/temuan',                                   [Auditor\TemuanController::class, 'index'])->name('temuan');
            Route::post('/temuan',                                  [Auditor\TemuanController::class, 'store'])->name('temuan.store');
            Route::put('/temuan/{temuan}',                          [Auditor\TemuanController::class, 'update'])->name('temuan.update');
            Route::delete('/temuan/{temuan}',                       [Auditor\TemuanController::class, 'destroy'])->name('temuan.destroy');

            Route::get('/tindak-lanjut',                            [Auditor\TindakLanjutController::class, 'index'])->name('tindak_lanjut');
            Route::get('/tindak-lanjut/{plan}',                     [Auditor\TindakLanjutController::class, 'show'])->name('tindak_lanjut.show');
            Route::post('/tindak-lanjut/{temuan}/kirim',            [Auditor\TindakLanjutController::class, 'kirimPesan'])->name('tindak_lanjut.kirim');
            Route::get('/tindak-lanjut/{temuan}/pesan',             [Auditor\TindakLanjutController::class, 'pesanTerbaru'])->name('tindak_lanjut.pesan');
        });

    /* ═══════════════ SUPERVISOR ═══════════════ */
    Route::prefix('supervisor')
        ->middleware('role:supervisor')
        ->name('supervisor.')
        ->group(function () {
            Route::get('/dashboard', [Supervisor\DashboardController::class, 'index'])->name('dashboard');

            Route::get('/audit',     [Supervisor\AuditController::class, 'index'])->name('audit.index');
            Route::get('/audit/{plan}', [Supervisor\AuditController::class, 'show'])->name('audit.show');

            Route::get('/persetujuan',                              [Supervisor\PersetujuanController::class, 'index'])->name('persetujuan.index');
            Route::get('/persetujuan/{approval}',                   [Supervisor\PersetujuanController::class, 'show'])->name('persetujuan.show');
            Route::post('/persetujuan/{approval}/setujui',          [Supervisor\PersetujuanController::class, 'setujui'])->name('persetujuan.setujui');
            Route::post('/persetujuan/{approval}/tolak',            [Supervisor\PersetujuanController::class, 'tolak'])->name('persetujuan.tolak');
            Route::get('/persetujuan/{approval}/download',          [Supervisor\PersetujuanController::class, 'download'])->name('persetujuan.download');
        });
});
