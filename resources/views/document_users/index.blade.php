@extends('layouts.app')

@section('page_title', 'Document Users')

@section('header_actions')
    <button class="btn btn-primary btn-sm" onclick="openModal('addUserModal')">
        <i class="fa-solid fa-user-plus"></i> Add New User
    </button>
@endsection

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-id-card" style="color: var(--color-primary);"></i>
                Document Users List
            </h2>
            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                Total Users: {{ count($users) }}
            </span>
        </div>

        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Phone Number</th>
                        <th>Notes</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 1rem; color: white;">
                                    <i class="fa-regular fa-user" style="color: var(--text-secondary); margin-right: 0.5rem;"></i>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->phone ?: 'N/A' }}</td>
                            <td>{{ Str::limit($user->notes, 30) ?: 'N/A' }}</td>
                            <td style="text-align: right;">
                                <div class="mobile-action-wrap">
                                    <a href="{{ route('document-users.show', $user->id) }}" class="btn btn-primary btn-sm btn-icon" title="View & Manage Documents">
                                        <i class="fa-solid fa-folder-open"></i> Manage Documents
                                    </a>

                                    <form action="{{ route('document-users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user and all their documents?');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon" type="submit" title="Delete User">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                <i class="fa-solid fa-id-card" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-muted);"></i>
                                No document users added yet. Click "+ Add New User" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-user-plus"></i> Add New User</h3>
                <button class="close-modal" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <form action="{{ route('document-users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_name">User Name</label>
                        <input type="text" name="name" id="add_name" class="form-control" placeholder="e.g. Sanjay Bhai" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_phone">Phone Number (Optional)</label>
                        <input type="text" name="phone" id="add_phone" class="form-control" placeholder="e.g. +91 9876543210">
                    </div>

                    <div class="form-group">
                        <label for="add_notes">Notes (Optional)</label>
                        <textarea name="notes" id="add_notes" class="form-control" rows="3" placeholder="Any additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
@endsection
