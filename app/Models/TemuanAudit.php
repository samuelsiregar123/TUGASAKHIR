<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemuanAudit extends Model
{
    use HasFactory;

    protected $table = 'temuan_audit';

    protected $fillable = [
        'audit_plan_id',
        'auditor_id',
        'butir_id',
        'judul',
        'deskripsi',
        'risiko',
        'rekomendasi',
        'deadline',
        'status_tindak_lanjut',
        'sumber',
        'jenis_kelemahan',
        'is_lengkap',
        'is_aktif',
    ];

    protected $casts = [
        'deadline'        => 'date',
        'jenis_kelemahan' => 'array',
        'is_lengkap'      => 'boolean',
        'is_aktif'        => 'boolean',
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function butir()
    {
        return $this->belongsTo(ButirPenilaian::class, 'butir_id');
    }

    public function pesanTindakLanjut()
    {
        return $this->hasMany(PesanTindakLanjut::class, 'temuan_id');
    }
}
