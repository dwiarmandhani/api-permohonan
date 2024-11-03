<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'nasabah_id',
        'no_aplikasi',
        'tanggal_aplikasi',
        'nama_ao',
        'jumlah_penghasilan',
        'jumlah_permohonan',
        'jumlah_penghasilan_lainnya',
        'jangka_waktu',
        'maksimal_pembiayaan',
        'tujuan_pembiayaan',
        'status_perkawinan',
        'upload_npwp',
        'slip_gaji',
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function financingRequest()
    {
        return $this->hasOne(FinancingRequest::class);
    }
    public function job()
    {
        return $this->hasOne(Job::class, 'nasabah_id', 'nasabah_id'); // Sesuaikan dengan relasi yang benar
    }
}

