<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditee_id',
        'nama_instansi',
        'url_target',
        'daftar_tim',
        'path_nda',
        'status',
        'alasan_tolak',
    ];

    public function auditee()
    {
        return $this->belongsTo(User::class, 'auditee_id');
    }

    public function auditPlans()
    {
        return $this->hasMany(AuditPlan::class);
    }
}
