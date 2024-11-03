<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'total_angsuran_biaya', 'jangka_waktu', 'cabang', 'capem'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

