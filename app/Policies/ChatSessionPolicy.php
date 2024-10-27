<?php

namespace App\Policies;

use App\Models\ChatSession;
use App\Models\User;

class ChatSessionPolicy
{
    public function view(User $user, ChatSession $chatSession)
    {
        return $user->id === $chatSession->user_id;
    }

    public function create(User $user)
    {
        return true; // Authenticated users can create chat sessions
    }
}