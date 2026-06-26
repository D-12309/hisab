<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Renter;
use App\Models\ExpenseParty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        // Get all documents with their related owner models
        $documents = Document::with('documentable')->latest()->get();
        
        // Get lists of potential owners for the upload form
        $renters = Renter::orderBy('name')->get();
        $expenseParties = ExpenseParty::orderBy('name')->get();

        return view('documents.index', compact('documents', 'renters', 'expenseParties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_file' => 'required|file|max:10240', // 10MB max
            'owner_type' => 'required|in:renter,expense_party',
            'owner_id' => 'required|integer',
        ]);

        if ($request->owner_type === 'renter') {
            $owner = Renter::findOrFail($request->owner_id);
        } else {
            $owner = ExpenseParty::findOrFail($request->owner_id);
        }

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Store securely in storage/app/public/documents
            $filePath = $file->storeAs('documents', $fileName, 'public');
            
            $owner->documents()->create([
                'title' => $request->title,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);

            return redirect()->route('documents.index')->with('success', 'Document uploaded successfully.');
        }

        return back()->with('error', 'Failed to upload document.');
    }

    public function download(Document $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
        }

        return redirect()->route('documents.index')->with('error', 'File not found on server.');
    }

    public function destroy(Document $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted securely.');
    }
}
