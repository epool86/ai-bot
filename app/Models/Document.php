<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file_path',
        'file_type',
        'content',
        'content_vector',
    ];

    protected $casts = [
        'content_vector' => 'array',
    ];

    public function knowledgeSet()
    {
        return $this->belongsTo(KnowledgeSet::class);
    }
}
