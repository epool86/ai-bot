<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\KnowledgeSet;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(KnowledgeSet $knowledgeSet)
    {
        $sessions = $knowledgeSet->chatSessions()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('chat.index', compact('knowledgeSet', 'sessions'));
    }

    public function show(ChatSession $chatSession)
    {
        $this->authorize('view', $chatSession);
        return view('chat.show', compact('chatSession'));
    }

    public function store(Request $request, KnowledgeSet $knowledgeSet)
    {
        $session = $knowledgeSet->chatSessions()->create([
            'user_id' => auth()->id(),
            'title' => 'Chat Session ' . now()->format('Y-m-d H:i')
        ]);

        return redirect()->route('chat.show', $session);
    }

    public function message(Request $request, ChatSession $chatSession)
    {
        $this->authorize('view', $chatSession);

        $request->validate([
            'message' => 'required|string'
        ]);

        try {
            $result = $this->chatService->generateResponse(
                $request->message,
                $chatSession
            );

            $message = $chatSession->messages()->create([
                'message' => $request->message,
                'response' => $result['response'],
                'used_documents' => $result['used_documents']
            ]);

            return response()->json([
                'message' => $message->message,
                'response' => $message->response,
                'timestamp' => $message->created_at->format('M d, Y H:i')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate response: ' . $e->getMessage()
            ], 500);
        }
    }
}