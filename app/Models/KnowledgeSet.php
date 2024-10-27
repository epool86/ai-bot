<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }
    
}
