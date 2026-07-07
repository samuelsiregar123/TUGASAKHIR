<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesanTindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'pesan_tindak_lanjut';

    protected $fillable = [
        'temuan_id',
        'user_id',
        'pesan',
        'path_bukti',
        'lampiran',
    ];

    public function temuan()
    {
        return $this->belongsTo(TemuanAudit::class, 'temuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
