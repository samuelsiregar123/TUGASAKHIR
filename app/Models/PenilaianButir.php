<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianButir extends Model
{
    use HasFactory;

    protected $table = 'penilaian_butir';

    protected $fillable = [
        'audit_plan_id',
        'auditor_id',
        'butir_id',
        'jawaban_auditee',
        'edk',
        'catatan_edk',
        'eik',
        'catatan_eik',
        'efk',
        'catatan_efk',
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

    public function buktiButir()
    {
        return $this->hasMany(BuktiButir::class, 'penilaian_id');
    }
}
