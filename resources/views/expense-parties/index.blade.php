@extends('layouts.app')

@section('page_title', 'Expense Parties')

@section('header_actions')
    <button class="btn btn-primary btn-sm" onclick="openModal('addPartyModal')">
        <i class="fa-solid fa-handshake-simple"></i> Add Expense Party
    </button>
@endsection

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-handshake" style="color: var(--color-primary);"></i>
                Registered Expense Parties (Vendors)
            </h2>
            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                Total Parties: {{ count($parties) }}
            </span>
        </div>

        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Party Name</th>
                        <th>Contact Number</th>
                        <th>Total Expense Amount</th>
                        <th>Registered Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parties as $party)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 1rem; color: white;">
                                    <i class="fa-solid fa-briefcase" style="color: var(--text-secondary); margin-right: 0.5rem;"></i>
                                    {{ $party->name }}
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary">
                                    {{ $party->phone ?: 'N/A' }}
                                </span>
                            </td>
                            <td style="font-weight: 700; color: var(--color-danger);">
                                ₹{{ number_format($party->transactions_sum_amount ?: 0, 2) }}
                            </td>
                            <td style="color: var(--text-secondary);">
                                {{ $party->created_at->format('M d, Y') }}
                            </td>
                            <td style="text-align: right;">
                                <div class="mobile-action-wrap">
                                    <!-- Record Expense Quick Action -->
                                    <button class="btn btn-danger btn-sm btn-icon" 
                                            onclick="triggerExpenseModal(this)"
                                            data-id="{{ $party->id }}"
                                            data-name="{{ $party->name }}"
                                            title="Log Expense for Party">
                                        <i class="fa-solid fa-minus-circle"></i> Add Expense
                                    </button>

                                    <!-- Edit Expense Party Details -->
                                    <button class="btn btn-secondary btn-sm btn-icon"
                                            onclick="triggerEditModal(this)"
                                            data-id="{{ $party->id }}"
                                            data-name="{{ $party->name }}"
                                            data-phone="{{ $party->phone }}"
                                            title="Edit Party Details">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>

                                    <!-- Delete Expense Party -->
                                    <form action="{{ route('expense-parties.destroy', $party->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense party? Linked transactions will remain but become unassociated.');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon" type="submit" title="Delete Party">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                <i class="fa-solid fa-handshake-slash" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-muted);"></i>
                                No expense parties created yet. Click "+ Add Expense Party" to register one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. Add Expense Party Modal -->
    <div class="modal-overlay" id="addPartyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-handshake-simple"></i> Add Expense Party</h3>
                <button class="close-modal" onclick="closeModal('addPartyModal')">&times;</button>
            </div>
            <form action="{{ route('expense-parties.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_name">Party / Vendor Name</label>
                        <input type="text" name="name" id="add_name" class="form-control" placeholder="e.g. Electrician Sharma, Municipal Tax Office" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_phone">Phone / Contact Number (Optional)</label>
                        <input type="text" name="phone" id="add_phone" class="form-control" placeholder="e.g. +91 98765 43210">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addPartyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Vendor Party</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Expense Party Modal -->
    <div class="modal-overlay" id="editPartyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-user-pen"></i> Edit Expense Party</h3>
                <button class="close-modal" onclick="closeModal('editPartyModal')">&times;</button>
            </div>
            <form id="editPartyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Party / Vendor Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_phone">Phone / Contact Number (Optional)</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editPartyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Record Expense Modal -->
    <div class="modal-overlay" id="recordExpenseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" style="color: var(--color-danger);"><i class="fa-solid fa-minus-circle"></i> Log Expense for Party</h3>
                <button class="close-modal" onclick="closeModal('recordExpenseModal')">&times;</button>
            </div>
            <form id="recordExpenseForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom: 1.25rem; font-size: 0.95rem; color: var(--text-secondary);">
                        Record a new expense for vendor: <strong id="expense_party_label" style="color: white;"></strong>.
                    </p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="expense_date">Expense Date</label>
                            <input type="date" name="date" id="expense_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="expense_amount">Amount Paid (₹)</label>
                            <input type="number" step="0.01" name="amount" id="expense_amount" class="form-control" placeholder="1000.00" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="expense_category">Category</label>
                            <input type="text" name="category" id="expense_category" class="form-control" list="expense_categories" placeholder="e.g. Repairs, Wages, Cleaning" required>
                            <datalist id="expense_categories">
                                <option value="Maintenance">
                                <option value="Utilities">
                                <option value="Electricity">
                                <option value="Salary / Wages">
                                <option value="Taxes">
                                <option value="Other">
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="expense_payment_method">Payment Mode</label>
                            <select name="payment_method" id="expense_payment_method" class="form-control" onchange="toggleBankerField('expense')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="expense_banker_container" style="display: none;">
                        <label for="expense_banker_name">Banker Name</label>
                        <select name="banker_name" id="expense_banker_name" class="form-control">
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="expense_description">Description (Optional)</label>
                        <input type="text" name="description" id="expense_description" class="form-control" placeholder="e.g. Office repair work">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('recordExpenseModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Log Expense Entry</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Toggle Banker Field Visibility
        function toggleBankerField(prefix) {
            const methodSelect = document.getElementById(`${prefix}_payment_method`);
            const container = document.getElementById(`${prefix}_banker_container`);
            const input = document.getElementById(`${prefix}_banker_name`);
            
            if (methodSelect && methodSelect.value === 'bank_transfer') {
                container.style.display = 'block';
                if (input) {
                    input.setAttribute('required', 'required');
                }
            } else {
                container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '';
                }
            }
        }

        // Trigger Edit Modal and Populate Fields
        function triggerEditModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const phone = button.getAttribute('data-phone');

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_phone').value = phone || '';

            // Update form action dynamically
            document.getElementById('editPartyForm').action = `/expense-parties/${id}`;

            openModal('editPartyModal');
        }

        // Trigger Add Expense Modal for Vendor
        function triggerExpenseModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');

            document.getElementById('expense_party_label').innerText = name;
            document.getElementById('expense_amount').value = '';
            document.getElementById('expense_date').value = new Date().toISOString().split('T')[0]; // Today
            document.getElementById('expense_category').value = '';
            document.getElementById('expense_description').value = `Expense transaction for ${name}`;
            document.getElementById('expense_payment_method').value = 'cash';
            
            // Reset banker field visibility
            toggleBankerField('expense');

            // Update form action dynamically
            document.getElementById('recordExpenseForm').action = `/expense-parties/${id}/expense`;

            openModal('recordExpenseModal');
        }
    </script>
@endsection
