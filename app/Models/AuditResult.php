<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id',
        'nilai_edk_tk',
        'nilai_eik_tk',
        'nilai_efk_tk',
        'konklusi_tk',
        'nilai_edk_mk',
        'nilai_eik_mk',
        'nilai_efk_mk',
        'konklusi_mk',
        'nilai_edk_fk',
        'nilai_eik_fk',
        'nilai_efk_fk',
        'konklusi_fk',
        'konklusi_keseluruhan',
        'path_lhak',
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }
}
