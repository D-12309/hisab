<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'notes',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
