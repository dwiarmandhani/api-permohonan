<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'nasabah_id', 'nama_instansi', 'no_instansi', 'golongan_jabatan',
        'nip', 'masa_kerja_hari', 'masa_kerja_bulan', 'masa_kerja_tahun',
        'nama_atasan', 'alamat_kantor'
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class);
    }
}

