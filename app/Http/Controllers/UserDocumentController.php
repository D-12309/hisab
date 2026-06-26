<?php

namespace App\Http\Controllers;

use App\Models\DocumentUser;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserDocumentController extends Controller
{
    public function store(Request $request, DocumentUser $documentUser)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Store securely in storage/app/public/documents
            $filePath = $file->storeAs('documents', $fileName, 'public');
            
            $documentUser->documents()->create([
                'title' => $request->title,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);

            return back()->with('success', 'Document uploaded successfully.');
        }

        return back()->with('error', 'Failed to upload document.');
    }

    public function view(DocumentUser $documentUser, Document $document)
    {
        // Ensure the document belongs to the user
        if ($document->document_user_id !== $documentUser->id) {
            abort(403);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            $path = Storage::disk('public')->path($document->file_path);
            return response()->file($path);
        }

        return back()->with('error', 'File not found on server.');
    }

    public function download(DocumentUser $documentUser, Document $document)
    {
        // Ensure the document belongs to the user
        if ($document->document_user_id !== $documentUser->id) {
            abort(403);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
        }

        return back()->with('error', 'File not found on server.');
    }

    public function destroy(DocumentUser $documentUser, Document $document)
    {
        // Ensure the document belongs to the user
        if ($document->document_user_id !== $documentUser->id) {
            abort(403);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return back()->with('success', 'Document deleted securely.');
    }
}
