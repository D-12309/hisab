@extends('layouts.app')

@section('page_title', 'Bankers Management')

@section('header_actions')
    <button class="btn btn-primary btn-sm" onclick="openModal('addBankModal')">
        <i class="fa-solid fa-building-columns"></i> Add New Banker
    </button>
@endsection

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-university" style="color: var(--color-primary);"></i>
                Registered Bank Accounts / Bankers
            </h2>
            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                Total Bankers: {{ count($banks) }}
            </span>
        </div>

        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Bank / Banker Name</th>
                        <th>Registered Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $bank)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 1rem; color: white;">
                                    <i class="fa-solid fa-building-columns" style="color: var(--text-secondary); margin-right: 0.5rem;"></i>
                                    {{ $bank->name }}
                                </div>
                            </td>
                            <td style="color: var(--text-secondary);">
                                {{ $bank->created_at->format('M d, Y') }}
                            </td>
                            <td style="text-align: right;">
                                <div class="mobile-action-wrap">
                                    <!-- Edit Banker Details -->
                                    <button class="btn btn-secondary btn-sm btn-icon"
                                            onclick="triggerEditModal(this)"
                                            data-id="{{ $bank->id }}"
                                            data-name="{{ $bank->name }}"
                                            title="Edit Banker Name">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>

                                    <!-- Delete Banker -->
                                    <form action="{{ route('banks.destroy', $bank->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Banker? This will not affect existing transactions but this Banker will disappear from active selection lists.');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon" type="submit" title="Delete Banker">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                <i class="fa-solid fa-university" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-muted);"></i>
                                No bankers registered yet. Click "+ Add New Banker" to register one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. Add Bank Modal -->
    <div class="modal-overlay" id="addBankModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-building-columns"></i> Add New Banker</h3>
                <button class="close-modal" onclick="closeModal('addBankModal')">&times;</button>
            </div>
            <form action="{{ route('banks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_name">Bank / Banker Name</label>
                        <input type="text" name="name" id="add_name" class="form-control" placeholder="e.g. HDFC Bank, SBI Bank, ICICI Bank" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addBankModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Banker</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Bank Modal -->
    <div class="modal-overlay" id="editBankModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-pen-to-square"></i> Edit Banker Name</h3>
                <button class="close-modal" onclick="closeModal('editBankModal')">&times;</button>
            </div>
            <form id="editBankForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Bank / Banker Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editBankModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Trigger Edit Modal and Populate Fields
        function triggerEditModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');

            document.getElementById('edit_name').value = name;

            // Update form action dynamically
            document.getElementById('editBankForm').action = `/banks/${id}`;

            openModal('editBankModal');
        }
    </script>
@endsection
