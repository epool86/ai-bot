<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'response', 'used_documents'];

    protected $casts = [
        'used_documents' => 'array'
    ];

    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }
    
}
