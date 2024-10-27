<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\DocumentProcessor;
use App\Services\TogetherAiService;

use App\Models\Document;
use App\Models\KnowledgeSet;

class DocumentController extends Controller
{

    private $documentProcessor;
    private $togetherAi;

    public function __construct(DocumentProcessor $documentProcessor, TogetherAiService $togetherAi)
    {
        $this->documentProcessor = $documentProcessor;
        $this->togetherAi = $togetherAi;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(KnowledgeSet $knowledgeSet)
    {
        $documents = $knowledgeSet->documents()->latest()->get();
        return view('documents.index', compact('knowledgeSet', 'documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(KnowledgeSet $knowledgeSet)
    {
        return view('documents.create', compact('knowledgeSet'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, KnowledgeSet $knowledgeSet)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,doc,docx,txt|max:10240',
        ]);

        try {
            $file = $request->file('document');
            $path = $file->store('documents', 'public');
            $fileType = $file->getClientOriginalExtension();

            // Extract text content
            Log::info('Extracting text from document', ['path' => $path, 'type' => $fileType]);
            $content = $this->documentProcessor->extractText($path, $fileType);
            
            if (empty($content)) {
                throw new \Exception('No text content could be extracted from the document');
            }

            // Create embedding vector
            Log::info('Creating embedding for content', ['content_length' => strlen($content)]);
            $embedding = $this->togetherAi->createEmbedding($content);
            
            if (empty($embedding)) {
                throw new \Exception('Failed to generate embedding vector');
            }

            Log::info('Embedding created successfully', [
                'vector_length' => count($embedding),
            ]);

            // Create document record
            $document = $knowledgeSet->documents()->create([
                'title' => $request->title,
                'file_path' => $path,
                'file_type' => $fileType,
                'content' => $content,
                'content_vector' => $embedding,
            ]);

            Log::info('Document created successfully', [
                'id' => $document->id,
                'has_vector' => !empty($document->content_vector),
            ]);

            return redirect()->route('knowledge-sets.documents.index', $knowledgeSet)
                ->with('success', 'Document uploaded and processed successfully.');

        } catch (\Exception $e) {
            Log::error('Document processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()->withErrors(['error' => 'Failed to process document: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KnowledgeSet $knowledgeSet, Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('knowledge-sets.documents.index', $knowledgeSet)
            ->with('success', 'Document deleted successfully.');
    }
}
