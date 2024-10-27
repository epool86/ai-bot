<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'user_id', 'knowledge_set_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function knowledgeSet()
    {
        return $this->belongsTo(KnowledgeSet::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
