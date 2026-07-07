<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id',
        'tool',
        'target_url',
        'status',
        'hasil_json',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'hasil_json'  => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function auditPlan()
    {
        return $this->belongsTo(AuditPlan::class);
    }
}
