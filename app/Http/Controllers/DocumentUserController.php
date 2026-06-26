<?php

namespace App\Http\Controllers;

use App\Models\DocumentUser;
use Illuminate\Http\Request;

class DocumentUserController extends Controller
{
    public function index()
    {
        $users = DocumentUser::orderBy('name')->get();
        return view('document_users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        DocumentUser::create($request->all());

        return redirect()->route('document-users.index')->with('success', 'User created successfully.');
    }

    public function show(DocumentUser $documentUser)
    {
        $documentUser->load('documents');
        return view('document_users.show', compact('documentUser'));
    }

    public function destroy(DocumentUser $documentUser)
    {
        $documentUser->delete();
        return redirect()->route('document-users.index')->with('success', 'User deleted successfully.');
    }
}
