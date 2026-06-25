@extends('layouts.app')

@section('page_title', 'Renters Management')

@section('header_actions')
    <button class="btn btn-primary btn-sm" onclick="openModal('addRenterModal')">
        <i class="fa-solid fa-user-plus"></i> Add New Renter
    </button>
@endsection

@section('content')
    <!-- Renters Summary Cards / Table -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-users" style="color: var(--color-primary);"></i>
                Active Renters List
            </h2>
            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                Total Renters: {{ count($renters) }}
            </span>
        </div>

        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Renter Name</th>
                        <th>Monthly Rent</th>
                        <th>Rent Payment Preference</th>
                        <th>Advance Deposit Held</th>
                        <th>Total Rent Paid</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($renters as $renter)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 1rem; color: white;">
                                    <i class="fa-regular fa-user" style="color: var(--text-secondary); margin-right: 0.5rem;"></i>
                                    {{ $renter->name }}
                                </div>
                            </td>
                            <td style="font-weight: 600;">₹{{ number_format($renter->rent_amount, 2) }}</td>
                            <td>
                                @if($renter->payment_method === 'bank_transfer')
                                    <span class="badge badge-bank"><i class="fa-solid fa-university"></i> Bank</span>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">
                                        {{ $renter->banker_name }}
                                    </div>
                                @else
                                    <span class="badge badge-cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight: 700; color: {{ $renter->deposit_amount > 0 ? 'var(--color-warning)' : 'var(--text-muted)' }}">
                                    ₹{{ number_format($renter->deposit_amount, 2) }}
                                </span>
                                @if($renter->deposit_amount > 0)
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">
                                        @if($renter->deposit_payment_method === 'bank_transfer')
                                            <i class="fa-solid fa-university" style="font-size: 0.7rem;"></i> Bank ({{ $renter->deposit_banker_name }})
                                        @else
                                            <i class="fa-solid fa-money-bill-wave" style="font-size: 0.7rem;"></i> Cash
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="font-weight: 700; color: var(--color-success);">
                                ₹{{ number_format($renter->transactions_sum_amount ?: 0, 2) }}
                            </td>
                            <td style="text-align: right;">
                                <div class="mobile-action-wrap">
                                    <!-- Record Rent Payment Quick Action -->
                                    <button class="btn btn-success btn-sm btn-icon" 
                                            onclick="triggerPaymentModal(this)"
                                            data-id="{{ $renter->id }}"
                                            data-name="{{ $renter->name }}"
                                            data-amount="{{ $renter->rent_amount }}"
                                            data-method="{{ $renter->payment_method }}"
                                            data-banker="{{ $renter->banker_name }}"
                                            title="Record Rent Payment">
                                        <i class="fa-solid fa-hand-holding-dollar"></i> Collect Rent
                                    </button>

                                    <!-- Refund Deposit Quick Action -->
                                    @if($renter->deposit_amount > 0)
                                        <button class="btn btn-warning btn-sm btn-icon" 
                                                onclick="triggerRefundModal(this)"
                                                data-id="{{ $renter->id }}"
                                                data-name="{{ $renter->name }}"
                                                data-amount="{{ $renter->deposit_amount }}"
                                                data-method="{{ $renter->deposit_payment_method }}"
                                                data-banker="{{ $renter->deposit_banker_name }}"
                                                title="Refund Security Deposit"
                                                style="color: black;">
                                            <i class="fa-solid fa-rotate-left"></i> Refund Deposit
                                        </button>
                                    @endif

                                    <!-- Edit Renter Details -->
                                    <button class="btn btn-secondary btn-sm btn-icon"
                                            onclick="triggerEditModal(this)"
                                            data-id="{{ $renter->id }}"
                                            data-name="{{ $renter->name }}"
                                            data-amount="{{ $renter->rent_amount }}"
                                            data-method="{{ $renter->payment_method }}"
                                            data-banker="{{ $renter->banker_name }}"
                                            data-deposit-amount="{{ $renter->deposit_amount }}"
                                            data-deposit-method="{{ $renter->deposit_payment_method }}"
                                            data-deposit-banker="{{ $renter->deposit_banker_name }}"
                                            title="Edit Profile">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Renter Profile -->
                                    <form action="{{ route('renters.destroy', $renter->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this renter? All linked transactions will remain but become unassociated.');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon" type="submit" title="Delete Profile">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                <i class="fa-solid fa-users-slash" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-muted);"></i>
                                No renter profiles created yet. Click "+ Add New Renter" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. Add Renter Modal -->
    <div class="modal-overlay" id="addRenterModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-user-plus"></i> Add New Renter</h3>
                <button class="close-modal" onclick="closeModal('addRenterModal')">&times;</button>
            </div>
            <form action="{{ route('renters.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <h5 style="color: #818cf8; margin-bottom: 0.75rem; font-family: var(--font-heading); font-size: 1rem;">1. Renter Profile</h5>
                    <div class="form-group">
                        <label for="add_name">Renter Name</label>
                        <input type="text" name="name" id="add_name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_rent_amount">Monthly Rent Amount (₹)</label>
                            <input type="number" step="0.01" name="rent_amount" id="add_rent_amount" class="form-control" placeholder="5000.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_payment_method">Rent Payment Preference</label>
                            <select name="payment_method" id="add_payment_method" class="form-control" onchange="toggleBankerField('add')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="add_banker_container" style="display: none;">
                        <label for="add_banker_name">Banker Name</label>
                        <select name="banker_name" id="add_banker_name" class="form-control">
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <h5 style="color: #fbbf24; margin-top: 1.5rem; margin-bottom: 0.75rem; font-family: var(--font-heading); font-size: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">2. Security Deposit Details</h5>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_deposit_amount">Advance Deposit Amount (₹)</label>
                            <input type="number" step="0.01" name="deposit_amount" id="add_deposit_amount" class="form-control" value="0.00" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_deposit_payment_method">Deposit Mode</label>
                            <select name="deposit_payment_method" id="add_deposit_payment_method" class="form-control" onchange="toggleDepositBankerField('add')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="add_deposit_banker_container" style="display: none;">
                        <label for="add_deposit_banker_name">Deposit Banker Name</label>
                        <select name="deposit_banker_name" id="add_deposit_banker_name" class="form-control">
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addRenterModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Renter Modal -->
    <div class="modal-overlay" id="editRenterModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-user-pen"></i> Edit Renter Details</h3>
                <button class="close-modal" onclick="closeModal('editRenterModal')">&times;</button>
            </div>
            <form id="editRenterForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <h5 style="color: #818cf8; margin-bottom: 0.75rem; font-family: var(--font-heading); font-size: 1rem;">1. Renter Profile</h5>
                    <div class="form-group">
                        <label for="edit_name">Renter Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_rent_amount">Monthly Rent Amount (₹)</label>
                            <input type="number" step="0.01" name="rent_amount" id="edit_rent_amount" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_payment_method">Rent Payment Preference</label>
                            <select name="payment_method" id="edit_payment_method" class="form-control" onchange="toggleBankerField('edit')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="edit_banker_container" style="display: none;">
                        <label for="edit_banker_name">Banker Name</label>
                        <select name="banker_name" id="edit_banker_name" class="form-control">
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <h5 style="color: #fbbf24; margin-top: 1.5rem; margin-bottom: 0.75rem; font-family: var(--font-heading); font-size: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">2. Security Deposit Details</h5>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_deposit_amount">Advance Deposit Amount (₹)</label>
                            <input type="number" step="0.01" name="deposit_amount" id="edit_deposit_amount" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_deposit_payment_method">Deposit Mode</label>
                            <select name="deposit_payment_method" id="edit_deposit_payment_method" class="form-control" onchange="toggleDepositBankerField('edit')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="edit_deposit_banker_container" style="display: none;">
                        <label for="edit_deposit_banker_name">Deposit Banker Name</label>
                        <select name="deposit_banker_name" id="edit_deposit_banker_name" class="form-control">
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editRenterModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Record Rent Payment Quick Modal -->
    <div class="modal-overlay" id="recordPaymentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" style="color: var(--color-success);"><i class="fa-solid fa-hand-holding-dollar"></i> Log Rent Payment</h3>
                <button class="close-modal" onclick="closeModal('recordPaymentModal')">&times;</button>
            </div>
            <form id="recordPaymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom: 1.25rem; font-size: 0.95rem; color: var(--text-secondary);">
                        Record a new rent transaction for <strong id="payment_renter_label" style="color: white;"></strong>.
                    </p>
                    
                    <div class="form-group">
                        <label style="font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.4rem; display: block;">Select Month(s) Paid For</label>
                        <div class="months-checkbox-grid">
                            @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $m)
                                <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; cursor: pointer; color: white;">
                                    <input type="checkbox" name="months[]" value="{{ $m }} {{ date('Y') }}" class="payment-month-check" onchange="updateCollectedAmount()">
                                    {{ $m }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="payment_date">Payment Date</label>
                            <input type="date" name="date" id="payment_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_amount">Amount Collected (₹)</label>
                            <input type="number" step="0.01" name="amount" id="payment_amount" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="payment_method">Payment Mode</label>
                            <select name="payment_method" id="payment_method" class="form-control" onchange="toggleBankerField('payment')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group" id="payment_banker_container" style="display: none;">
                            <label for="payment_banker_name">Banker Name</label>
                            <select name="banker_name" id="payment_banker_name" class="form-control">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_description">Description (Optional)</label>
                        <input type="text" name="description" id="payment_description" class="form-control" placeholder="e.g. Monthly Rent for June 2026">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('recordPaymentModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Log Income Entry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. Refund Security Deposit Modal -->
    <div class="modal-overlay" id="refundDepositModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" style="color: var(--color-warning);"><i class="fa-solid fa-rotate-left"></i> Refund Security Deposit</h3>
                <button class="close-modal" onclick="closeModal('refundDepositModal')">&times;</button>
            </div>
            <form id="refundDepositForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom: 1.25rem; font-size: 0.95rem; color: var(--text-secondary);">
                        Refund security deposit back to <strong id="refund_renter_label" style="color: white;"></strong>.
                    </p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="refund_date">Refund Date</label>
                            <input type="date" name="date" id="refund_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="refund_amount">Refund Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" id="refund_amount" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="refund_method">Refund Mode</label>
                            <select name="payment_method" id="refund_method" class="form-control" onchange="toggleBankerField('refund')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group" id="refund_banker_container" style="display: none;">
                            <label for="refund_banker_name">Banker Name</label>
                            <select name="banker_name" id="refund_banker_name" class="form-control">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="refund_description">Description (Optional)</label>
                        <input type="text" name="description" id="refund_description" class="form-control" placeholder="e.g. Return of security deposit upon moving out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('refundDepositModal')">Cancel</button>
                    <button type="submit" class="btn btn-warning" style="color: black;">Log Expense Refund</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Toggle Rent Banker Field Visibility
        function toggleBankerField(prefix) {
            const methodSelect = document.getElementById(`${prefix}_payment_method` || `${prefix}_method`);
            const container = document.getElementById(`${prefix}_banker_container`);
            const input = document.getElementById(`${prefix}_banker_name`);
            
            // Adjust lookup for direct select id
            let selectedVal = methodSelect ? methodSelect.value : document.getElementById(`${prefix}_method`).value;

            if (selectedVal === 'bank_transfer') {
                container.style.display = 'block';
                input.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '';
                }
            }
        }

        // Toggle Deposit Banker Field Visibility
        function toggleDepositBankerField(prefix) {
            const methodSelect = document.getElementById(`${prefix}_deposit_payment_method`);
            const container = document.getElementById(`${prefix}_deposit_banker_container`);
            const input = document.getElementById(`${prefix}_deposit_banker_name`);
            
            if (methodSelect && methodSelect.value === 'bank_transfer') {
                container.style.display = 'block';
                input.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '';
                }
            }
        }

        // Trigger Edit Renter Modal
        function triggerEditModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const amount = button.getAttribute('data-amount');
            const method = button.getAttribute('data-method');
            const banker = button.getAttribute('data-banker');
            const depAmount = button.getAttribute('data-deposit-amount');
            const depMethod = button.getAttribute('data-deposit-method');
            const depBanker = button.getAttribute('data-deposit-banker');

            // Populate Form Fields
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_rent_amount').value = amount;
            document.getElementById('edit_payment_method').value = method;
            document.getElementById('edit_banker_name').value = banker || '';
            
            document.getElementById('edit_deposit_amount').value = depAmount || '0.00';
            document.getElementById('edit_deposit_payment_method').value = depMethod || 'cash';
            document.getElementById('edit_deposit_banker_name').value = depBanker || '';

            // Update form action dynamically
            document.getElementById('editRenterForm').action = `/renters/${id}`;

            // Adjust banker fields visibility
            toggleBankerField('edit');
            toggleDepositBankerField('edit');

            // Open Modal
            openModal('editRenterModal');
        }

        // Recalculate collected amount based on number of selected months
        function updateCollectedAmount() {
            const checkedCount = document.querySelectorAll('.payment-month-check:checked').length;
            const monthlyRent = parseFloat(document.getElementById('payment_amount').dataset.monthlyRent || 0);
            document.getElementById('payment_amount').value = (checkedCount * monthlyRent).toFixed(2);
            
            // Build month string for description
            const checkedMonths = Array.from(document.querySelectorAll('.payment-month-check:checked')).map(el => el.value.split(' ')[0]);
            const renterName = document.getElementById('payment_renter_label').innerText;
            if (checkedMonths.length > 0) {
                document.getElementById('payment_description').value = `Rent collection from ${renterName} for: ${checkedMonths.join(', ')}`;
            } else {
                document.getElementById('payment_description').value = `Rent collection from ${renterName}`;
            }
        }

        // Trigger Log Payment Modal
        function triggerPaymentModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const amount = button.getAttribute('data-amount');
            const method = button.getAttribute('data-method');
            const banker = button.getAttribute('data-banker');

            // Populate Payment Fields
            document.getElementById('payment_renter_label').innerText = name;
            
            // Save monthly rent on amount input dataset
            const amountInput = document.getElementById('payment_amount');
            amountInput.dataset.monthlyRent = amount;
            amountInput.value = amount; // default to 1 month rent

            document.getElementById('payment_method').value = method;
            document.getElementById('payment_banker_name').value = banker || '';
            document.getElementById('payment_date').value = new Date().toISOString().split('T')[0]; // Default to today
            
            // Pre-select current month by default
            const currentMonthName = new Date().toLocaleString('en-US', { month: 'short' }); // e.g. "Jun"
            const currentYear = new Date().getFullYear();
            const currentMonthValue = `${currentMonthName} ${currentYear}`;
            
            document.querySelectorAll('.payment-month-check').forEach(chk => {
                if (chk.value === currentMonthValue) {
                    chk.checked = true;
                } else {
                    chk.checked = false;
                }
            });

            // Set description based on current selection
            document.getElementById('payment_description').value = `Rent collection from ${name} for: ${currentMonthName}`;

            // Update form action dynamically
            document.getElementById('recordPaymentForm').action = `/renters/${id}/payment`;

            // Adjust banker visibility
            toggleBankerField('payment');

            // Open Modal
            openModal('recordPaymentModal');
        }

        // Trigger Refund Deposit Modal
        function triggerRefundModal(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const amount = button.getAttribute('data-amount');
            const method = button.getAttribute('data-method');
            const banker = button.getAttribute('data-banker');

            // Populate Refund Fields
            document.getElementById('refund_renter_label').innerText = name;
            document.getElementById('refund_amount').value = amount;
            document.getElementById('refund_method').value = method;
            document.getElementById('refund_banker_name').value = banker || '';
            document.getElementById('refund_date').value = new Date().toISOString().split('T')[0]; // Default to today
            document.getElementById('refund_description').value = `Refund security deposit to ${name} on exit`;

            // Update form action dynamically
            document.getElementById('refundDepositForm').action = `/renters/${id}/refund`;

            // Adjust banker visibility
            toggleBankerField('refund');

            // Open Modal
            openModal('refundDepositModal');
        }
    </script>
@endsection
