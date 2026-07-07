<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButirPenilaian extends Model
{
    use HasFactory;

    protected $table = 'butir_penilaian';

    protected $fillable = [
        'kode',
        'bagian',
        'nomor',
        'domain',
        'pertanyaan',
        'judul_butir',
        'sumber_acuan',
        'rujukan_mk',
        'ada_scan',
        'acuan_edk',
        'acuan_eik',
        'acuan_efk',
    ];

    protected $casts = [
        'ada_scan' => 'boolean',
    ];

    public function penilaianButir()
    {
        return $this->hasMany(PenilaianButir::class, 'butir_id');
    }

    public function temuanAudit()
    {
        return $this->hasMany(TemuanAudit::class, 'butir_id');
    }
}
