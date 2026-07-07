<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LhakApproval extends Model
{
    protected $fillable = [
        'audit_plan_id',
        'submitted_by',
        'reviewed_by',
        'status',
        'catatan',
        'file_lhak',
        'file_lhak_tte',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
