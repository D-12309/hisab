<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_user_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function documentUser()
    {
        return $this->belongsTo(DocumentUser::class);
    }
}
