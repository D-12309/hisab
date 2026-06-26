@extends('layouts.app')

@section('page_title', 'Manage Documents')

@section('header_actions')
    <a href="{{ route('document-users.index') }}" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
    <button class="btn btn-success btn-sm" onclick="openModal('uploadDocumentModal')">
        <i class="fa-solid fa-upload"></i> Upload Document
    </button>
@endsection

@section('content')
    <div class="content-card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-user" style="color: var(--color-primary);"></i>
                User Profile: {{ $documentUser->name }}
            </h2>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            <p><strong>Phone:</strong> {{ $documentUser->phone ?: 'N/A' }}</p>
            <p><strong>Notes:</strong> {{ $documentUser->notes ?: 'N/A' }}</p>
            <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--text-secondary);">Added on: {{ $documentUser->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-folder-open" style="color: var(--color-warning);"></i>
                Uploaded Documents
            </h2>
            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                Total Documents: {{ $documentUser->documents->count() }}
            </span>
        </div>

        <div class="document-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; padding: 1.5rem;">
            @forelse($documentUser->documents as $document)
                <div class="document-card" style="border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem 1rem; text-align: center; background: rgba(255,255,255,0.02); transition: all 0.2s ease;">
                    <div class="doc-icon" style="height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        @if(in_array(strtolower($document->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <img src="{{ route('document-users.documents.view', [$documentUser->id, $document->id]) }}" alt="{{ $document->title }}" style="max-height: 80px; max-width: 100%; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        @elseif(in_array(strtolower($document->file_type), ['pdf']))
                            <i class="fa-regular fa-file-pdf" style="color: #f87171; font-size: 3.5rem;"></i>
                        @elseif(in_array(strtolower($document->file_type), ['doc', 'docx']))
                            <i class="fa-regular fa-file-word" style="color: #3b82f6; font-size: 3.5rem;"></i>
                        @else
                            <i class="fa-regular fa-file-lines" style="color: #9ca3af; font-size: 3.5rem;"></i>
                        @endif
                    </div>
                    
                    <h4 style="font-size: 1rem; margin-bottom: 0.25rem; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $document->title }}">
                        {{ $document->title }}
                    </h4>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        {{ strtoupper($document->file_type) }} &bull; {{ number_format($document->file_size / 1024, 2) }} KB
                    </div>
                    
                    <div class="doc-actions" style="display: flex; justify-content: center; gap: 0.5rem;">
                        <a href="{{ route('document-users.documents.view', [$documentUser->id, $document->id]) }}" target="_blank" class="btn btn-secondary btn-sm btn-icon" title="View Document" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        
                        <a href="{{ route('document-users.documents.download', [$documentUser->id, $document->id]) }}" class="btn btn-primary btn-sm btn-icon" title="Download Document" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <form action="{{ route('document-users.documents.destroy', [$documentUser->id, $document->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document permanently?');" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm btn-icon" type="submit" title="Delete Document" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                    <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--border-color);"></i>
                    No documents uploaded yet. Click "Upload Document" to add files.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal-overlay" id="uploadDocumentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-upload"></i> Upload Document</h3>
                <button class="close-modal" onclick="closeModal('uploadDocumentModal')">&times;</button>
            </div>
            <form action="{{ route('document-users.documents.store', $documentUser->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom: 1.25rem; font-size: 0.95rem; color: var(--text-secondary);">
                        Uploading for user: <strong style="color: white;">{{ $documentUser->name }}</strong>
                    </p>

                    <div class="form-group">
                        <label for="title">Document Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Aadhar Card, PAN Card" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="document_file">Select File (Max 10MB)</label>
                        <input type="file" name="document_file" id="document_file" class="form-control" style="padding: 0.5rem;" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('uploadDocumentModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>
@endsection
