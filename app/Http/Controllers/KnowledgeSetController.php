<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\KnowledgeSet;

class KnowledgeSetController extends Controller
{
    public function index()
    {
        $knowledgeSets = auth()->user()->knowledgeSets()->latest()->get();
        return view('knowledge-sets.index', compact('knowledgeSets'));
    }

    public function create()
    {
        return view('knowledge-sets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        auth()->user()->knowledgeSets()->create($validated);

        return redirect()->route('knowledge-sets.index')->with('success', 'Knowledge set created successfully.');
    }

    public function edit(KnowledgeSet $knowledgeSet)
    {
        return view('knowledge-sets.edit', compact('knowledgeSet'));
    }

    public function update(Request $request, KnowledgeSet $knowledgeSet)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        $knowledgeSet->update($validated);

        return redirect()->route('knowledge-sets.index')->with('success', 'Knowledge set updated successfully.');
    }
}
