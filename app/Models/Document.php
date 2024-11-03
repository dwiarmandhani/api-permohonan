<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'dokumen_name', 'checklist_status', 'file_path',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

