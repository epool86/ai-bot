<?php

namespace App\Services;

use App\Models\Document;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private $togetherAi;
    private $vectorService;

    public function __construct(TogetherAiService $togetherAi, VectorService $vectorService)
    {
        $this->togetherAi = $togetherAi;
        $this->vectorService = $vectorService;
    }

    public function generateResponse(string $message, ChatSession $chatSession)
    {
        // Get embedding for the question
        $questionEmbedding = $this->togetherAi->createEmbedding($message);

        // Get relevant documents
        $allDocs = Document::where('knowledge_set_id', $chatSession->knowledge_set_id)->get();
        $similarDocs = $this->vectorService->findSimilarDocuments($questionEmbedding, $allDocs, 3);

        // Build context from similar documents
        $context = "Using the following information to answer the question:\n\n";
        $usedDocIds = [];
        
        foreach ($similarDocs as $doc) {
            $context .= $doc['document']->content . "\n\n";
            $usedDocIds[] = $doc['document']->id;
        }

        // Generate response using Together.ai
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.together.api_key'),
        ])->post('https://api.together.xyz/v1/chat/completions', [
            'model' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant. Answer questions based only on the provided context. If the answer cannot be found in the context, say "I cannot answer this based on the available information."'
                ],
                [
                    'role' => 'user',
                    'content' => $context . "\nQuestion: " . $message
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2500,
        ]);

        if (!$response->successful()) {
            Log::error('Chat completion failed', ['response' => $response->json()]);
            throw new \Exception('Failed to generate response');
        }

        $aiResponse = $response->json()['choices'][0]['message']['content'];

        return [
            'response' => $aiResponse,
            'used_documents' => $usedDocIds
        ];
    }
}