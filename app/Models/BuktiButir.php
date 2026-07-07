<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuktiButir extends Model
{
    use HasFactory;

    protected $table = 'bukti_butir';

    protected $fillable = [
        'penilaian_id',
        'jenis_acuan',
        'auditee_id',
        'path_file',
        'nama_file',
    ];

    public function penilaian()
    {
        return $this->belongsTo(PenilaianButir::class, 'penilaian_id');
    }

    public function auditee()
    {
        return $this->belongsTo(User::class, 'auditee_id');
    }
}
