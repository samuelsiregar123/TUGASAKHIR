<?php

namespace Database\Seeders;

use App\Models\AuditPlan;
use App\Models\BuktiButir;
use App\Models\ButirPenilaian;
use App\Models\PenilaianButir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class HasilKkaSeeder extends Seeder
{
    // Ganti sesuai audit_plan_id yang ingin diisi
    public int $planId = 1;

    private array $data = [
        ['kode' => 'TK-1',  'edk' => 'memadai',           'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'TK-2',  'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-3',  'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'TK-4',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-5',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-6',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-7',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-8',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-9',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-10', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-11', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-12', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-13', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-14', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-15', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-16', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-17', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-18', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-19', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-20', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-21', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-22', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'TK-23', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'TK-24', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'TK-25', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'efektif'],
        ['kode' => 'MK-1',  'edk' => 'memadai',           'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-2',  'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'MK-3',  'edk' => 'memadai',           'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-4',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'MK-5',  'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'MK-6',  'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'MK-7',  'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-8',  'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-9',  'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-10', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-11', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-12', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-13', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-14', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-15', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-16', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-17', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-18', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-19', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-20', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-21', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-22', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-23', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-24', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-25', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-26', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-27', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-28', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-29', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-30', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-31', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-32', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-33', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-34', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-35', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-36', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-37', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-38', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-39', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-40', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-41', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-42', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-43', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-44', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-45', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'MK-46', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'MK-47', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'efektif'],
        ['kode' => 'MK-48', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'MK-49', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'MK-50', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'belum_efektif'],
        ['kode' => 'FK-1',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-2',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-3',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-4',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-5',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-6',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-7',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-8',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-9',  'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-10', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-11', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-12', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-13', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-14', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-15', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-16', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-17', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-18', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-19', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-20', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-21', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-22', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-23', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-24', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-25', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-26', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'belum_efektif'],
        ['kode' => 'FK-27', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-28', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-29', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-30', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-31', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-32', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-33', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-34', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-35', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-36', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-37', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-38', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-39', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-40', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-41', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-42', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-43', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-44', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-45', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-46', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-47', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-48', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-49', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-50', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-51', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-52', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-53', 'edk' => 'memadai',           'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-54', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-55', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-56', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-57', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-58', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'efektif'],
        ['kode' => 'FK-59', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-60', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-61', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-62', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-63', 'edk' => 'perlu_peningkatan', 'eik' => 'sesuai',       'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-64', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-65', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-66', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-67', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-68', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-69', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-70', 'edk' => 'perlu_peningkatan', 'eik' => 'tidak_sesuai', 'efk' => 'perlu_peningkatan'],
        ['kode' => 'FK-71', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-72', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-73', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-74', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
        ['kode' => 'FK-75', 'edk' => 'tidak_memadai',     'eik' => null,           'efk' => 'belum_efektif'],
    ];

    public function run(): void
    {
        $plan = AuditPlan::findOrFail($this->planId);

        $auditorId = $plan->auditors()
            ->where('peran', 'anggota')
            ->value('user_id');

        if (!$auditorId) {
            $this->command->error("Tidak ada auditor anggota di audit_plan_id={$this->planId}.");
            return;
        }

        $butirMap = ButirPenilaian::pluck('id', 'kode');

        $missing = collect($this->data)
            ->pluck('kode')
            ->diff($butirMap->keys());

        if ($missing->isNotEmpty()) {
            $this->command->error('Kode butir tidak ditemukan di DB: ' . $missing->implode(', '));
            return;
        }

        PenilaianButir::where('audit_plan_id', $this->planId)->delete();

        $now = now();
        $rows = [];
        foreach ($this->data as $row) {
            $rows[] = [
                'audit_plan_id'  => $this->planId,
                'auditor_id'     => $auditorId,
                'butir_id'       => $butirMap[$row['kode']],
                'jawaban_auditee' => 'Tanggapan dummy untuk keperluan pengujian.',
                'edk'            => $row['edk'],
                'eik'            => $row['eik'],
                'efk'            => $row['efk'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        PenilaianButir::insert($rows);

        // Bukti dummy PDF
        $auditeeId = $plan->auditRequest->auditee_id;
        $dummyPdf  = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF";

        $insertedPenilaian = PenilaianButir::where('audit_plan_id', $this->planId)->get();
        $buktiRows = [];

        foreach ($insertedPenilaian as $pb) {
            $path = "bukti/{$this->planId}/{$pb->butir_id}/bukti_dummy.pdf";
            Storage::disk('public')->put($path, $dummyPdf);
            $buktiRows[] = [
                'penilaian_id' => $pb->id,
                'jenis_acuan'  => 'edk',
                'auditee_id'   => $auditeeId,
                'path_file'    => $path,
                'nama_file'    => 'bukti_dummy.pdf',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        BuktiButir::insert($buktiRows);

        $this->command->info("Selesai: " . count($rows) . " butir dan " . count($buktiRows) . " bukti dummy dimasukkan untuk audit_plan_id={$this->planId}.");
    }
}
