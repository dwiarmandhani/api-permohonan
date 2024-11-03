<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'alamat_lengkap', 'kelurahan', 'kecamatan', 'kabupaten', 'provinsi',
        'kode_pos', 'no_rekening_tabungan', 'no_hp', 'email', 'ktp'
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function job()
    {
        return $this->hasOne(Job::class);
    }
}

