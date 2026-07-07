<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_request_id',
        'waktu_mulai',
        'waktu_selesai',
        'status_pengisian',
    ];

    protected $casts = [
        'waktu_mulai'   => 'date',
        'waktu_selesai' => 'date',
    ];

    public function auditRequest()
    {
        return $this->belongsTo(AuditRequest::class);
    }

    public function auditors()
    {
        return $this->hasMany(AuditPlanAuditor::class);
    }

    public function penilaianButir()
    {
        return $this->hasMany(PenilaianButir::class);
    }

    public function scanResults()
    {
        return $this->hasMany(ScanResult::class);
    }

    public function temuanAudit()
    {
        return $this->hasMany(TemuanAudit::class);
    }

    public function auditResult()
    {
        return $this->hasOne(AuditResult::class);
    }
}
