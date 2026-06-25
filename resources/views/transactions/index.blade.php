@extends('layouts.app')

@section('page_title', 'Transactions Log (Hisab-Kitab)')

@section('header_actions')
    <button class="btn btn-success btn-sm" onclick="openModal('addIncomeModal')">
        <i class="fa-solid fa-plus-circle"></i> Log Income
    </button>
    <button class="btn btn-danger btn-sm" onclick="openModal('addExpenseModal')">
        <i class="fa-solid fa-minus-circle"></i> Log Expense
    </button>
@endsection

@section('content')
    <!-- Filters Toolbar Card -->
    <div class="content-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <form action="{{ route('transactions.index') }}" method="GET" class="filter-form">
            <div class="filter-group">
                <label for="filter_type" style="font-size: 0.75rem;">Transaction Type</label>
                <select name="type" id="filter_type" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">All Flow Types</option>
                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income (Inflow)</option>
                    <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense (Outflow)</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter_method" style="font-size: 0.75rem;">Payment Mode</label>
                <select name="payment_method" id="filter_method" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter_category" style="font-size: 0.75rem;">Category</label>
                <select name="category" id="filter_category" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.55rem 1rem;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->anyFilled(['type', 'payment_method', 'category']))
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm" style="padding: 0.55rem 1rem;">
                        <i class="fa-solid fa-xmark"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Ledgers Table -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-receipt" style="color: var(--color-primary);"></i>
                Transaction Ledger Book
            </h2>
            <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                Entries: {{ count($transactions) }}
            </span>
        </div>

        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Flow</th>
                        <th>Category</th>
                        <th>Party / Renter</th>
                        <th>Payment Mode</th>
                        <th>Banker Name</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <span style="font-weight: 600; color: white;">
                                    {{ $transaction->date->format('M d, Y') }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->type === 'income')
                                    <span class="badge badge-income"><i class="fa-solid fa-arrow-trend-up"></i> Income</span>
                                @else
                                    <span class="badge badge-expense"><i class="fa-solid fa-arrow-trend-down"></i> Expense</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight: 500; font-size: 0.85rem; color: #a5b4fc;">
                                    {{ $transaction->category }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: white;">
                                    @if($transaction->type === 'income')
                                        {{ $transaction->renter ? $transaction->renter->name : 'Other Inflow' }}
                                    @else
                                        {{ $transaction->expenseParty ? $transaction->expenseParty->name : 'Other Outflow' }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($transaction->payment_method === 'bank_transfer')
                                    <span class="badge badge-bank"><i class="fa-solid fa-university"></i> Bank</span>
                                @else
                                    <span class="badge badge-cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary" style="font-style: italic;">
                                    {{ $transaction->banker_name ?: 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary" style="font-size: 0.85rem;" title="{{ $transaction->description }}">
                                    {{ Str::limit($transaction->description, 28) ?: '-' }}
                                </span>
                            </td>
                            <td style="font-weight: 700; color: {{ $transaction->type === 'income' ? 'var(--color-success)' : 'var(--color-danger)' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}₹{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td style="text-align: right;">
                                <div class="mobile-action-wrap">
                                    <!-- Edit Entry Button -->
                                    <button class="btn btn-secondary btn-sm btn-icon"
                                            onclick="triggerEditModal(this)"
                                            data-id="{{ $transaction->id }}"
                                            data-type="{{ $transaction->type }}"
                                            data-amount="{{ $transaction->amount }}"
                                            data-date="{{ $transaction->date->format('Y-m-d') }}"
                                            data-method="{{ $transaction->payment_method }}"
                                            data-banker="{{ $transaction->banker_name }}"
                                            data-renter="{{ $transaction->renter_id }}"
                                            data-party="{{ $transaction->expense_party_id }}"
                                            data-category="{{ $transaction->category }}"
                                            data-desc="{{ $transaction->description }}"
                                            title="Edit Transaction">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Entry Form -->
                                    <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this bookkeeping entry?');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon" type="submit" title="Delete Entry">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                                <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: var(--text-muted);"></i>
                                No entries matched these filters. Try recording a new transaction!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. Log Income Modal -->
    <div class="modal-overlay" id="addIncomeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" style="color: var(--color-success);"><i class="fa-solid fa-plus-circle"></i> Log Income Entry</h3>
                <button class="close-modal" onclick="closeModal('addIncomeModal')">&times;</button>
            </div>
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="income">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_inc_date">Transaction Date</label>
                            <input type="date" name="date" id="add_inc_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="add_inc_amount">Amount Received (₹)</label>
                            <input type="number" step="0.01" name="amount" id="add_inc_amount" class="form-control" placeholder="12000.00" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_inc_renter">Link Renter (Optional)</label>
                            <select name="renter_id" id="add_inc_renter" class="form-control" onchange="autoFillRenterData()">
                                <option value="">-- Individual / Custom Inflow --</option>
                                @foreach($renters as $renter)
                                    <option value="{{ $renter->id }}" 
                                            data-amount="{{ $renter->rent_amount }}"
                                            data-method="{{ $renter->payment_method }}"
                                            data-banker="{{ $renter->banker_name }}">
                                        {{ $renter->name }} (Rent: ₹{{ $renter->rent_amount }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add_inc_category">Category</label>
                            <input type="text" name="category" id="add_inc_category" class="form-control" list="income_categories" placeholder="e.g. Rent, Interest, Refund" required>
                            <datalist id="income_categories">
                                <option value="Rent">
                                <option value="Salary">
                                <option value="Commission">
                                <option value="Interest">
                                <option value="Refund">
                                <option value="Other">
                            </datalist>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_inc_payment_method">Payment Mode</label>
                            <select name="payment_method" id="add_inc_payment_method" class="form-control" onchange="toggleBankerField('add_inc')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group" id="add_inc_banker_container" style="display: none;">
                            <label for="add_inc_banker_name">Banker Name</label>
                            <select name="banker_name" id="add_inc_banker_name" class="form-control">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="add_inc_description">Remarks / Description</label>
                        <textarea name="description" id="add_inc_description" class="form-control" rows="2" placeholder="Describe the source/details of payment"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addIncomeModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Income Inflow</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Log Expense Modal -->
    <div class="modal-overlay" id="addExpenseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" style="color: var(--color-danger);"><i class="fa-solid fa-minus-circle"></i> Log Expense Entry</h3>
                <button class="close-modal" onclick="closeModal('addExpenseModal')">&times;</button>
            </div>
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="expense">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_exp_date">Transaction Date</label>
                            <input type="date" name="date" id="add_exp_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="add_exp_amount">Amount Paid (₹)</label>
                            <input type="number" step="0.01" name="amount" id="add_exp_amount" class="form-control" placeholder="1500.00" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_exp_party">Link Expense Party (Optional)</label>
                            <select name="expense_party_id" id="add_exp_party" class="form-control">
                                <option value="">-- General / Custom Expense --</option>
                                @foreach($expenseParties as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add_exp_category">Category</label>
                            <input type="text" name="category" id="add_exp_category" class="form-control" list="expense_categories" placeholder="e.g. Repairs, Electricity, Cleaning" required>
                            <datalist id="expense_categories">
                                <option value="Maintenance">
                                <option value="Utilities">
                                <option value="Electricity">
                                <option value="Salary / Wages">
                                <option value="Taxes">
                                <option value="Internet / Phone">
                                <option value="Other">
                            </datalist>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_exp_payment_method">Payment Mode</label>
                            <select name="payment_method" id="add_exp_payment_method" class="form-control" onchange="toggleBankerField('add_exp')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="form-group" id="add_exp_banker_container" style="display: none;">
                            <label for="add_exp_banker_name">Banker Name</label>
                            <select name="banker_name" id="add_exp_banker_name" class="form-control">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="add_exp_description">Remarks / Description</label>
                        <textarea name="description" id="add_exp_description" class="form-control" rows="2" placeholder="Details of expense"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addExpenseModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Save Expense Outflow</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Edit Transaction Modal -->
    <div class="modal-overlay" id="editTransactionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" id="edit_title"><i class="fa-solid fa-pen-to-square"></i> Edit Transaction Entry</h3>
                <button class="close-modal" onclick="closeModal('editTransactionModal')">&times;</button>
            </div>
            <form id="editTransactionForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_date">Transaction Date</label>
                            <input type="date" name="date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_amount">Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- Renter Field (Visible only if type is income) -->
                        <div class="form-group" id="edit_renter_wrapper">
                            <label for="edit_renter">Associated Renter</label>
                            <select name="renter_id" id="edit_renter" class="form-control">
                                <option value="">-- None / Custom Inflow --</option>
                                @foreach($renters as $renter)
                                    <option value="{{ $renter->id }}">{{ $renter->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Expense Party Field (Visible only if type is expense) -->
                        <div class="form-group" id="edit_party_wrapper">
                            <label for="edit_party">Associated Expense Party</label>
                            <select name="expense_party_id" id="edit_party" class="form-control">
                                <option value="">-- None / Custom Outflow --</option>
                                @foreach($expenseParties as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_category">Category</label>
                            <input type="text" name="category" id="edit_category" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_payment_method">Payment Mode</label>
                            <select name="payment_method" id="edit_payment_method" class="form-control" onchange="toggleBankerField('edit')" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
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
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Remarks / Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editTransactionModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
                input.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                if (input) {
                    input.removeAttribute('required');
                    input.value = '';
                }
            }
        }

        // Auto-fill renter defaults when selected in the Income modal
        function autoFillRenterData() {
            const renterSelect = document.getElementById('add_inc_renter');
            const selectedOption = renterSelect.options[renterSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value !== "") {
                const amount = selectedOption.getAttribute('data-amount');
                const method = selectedOption.getAttribute('data-method');
                const banker = selectedOption.getAttribute('data-banker');

                document.getElementById('add_inc_amount').value = amount;
                document.getElementById('add_inc_payment_method').value = method;
                document.getElementById('add_inc_banker_name').value = banker || '';
                document.getElementById('add_inc_category').value = 'Rent';
                document.getElementById('add_inc_description').value = `Monthly rent collection from ${selectedOption.text.split('(')[0].trim()}`;
                
                toggleBankerField('add_inc');
            }
        }

        // Trigger Edit Modal and Populate Fields dynamically
        function triggerEditModal(button) {
            const id = button.getAttribute('data-id');
            const type = button.getAttribute('data-type');
            const amount = button.getAttribute('data-amount');
            const date = button.getAttribute('data-date');
            const method = button.getAttribute('data-method');
            const banker = button.getAttribute('data-banker');
            const renter = button.getAttribute('data-renter');
            const party = button.getAttribute('data-party');
            const category = button.getAttribute('data-category');
            const desc = button.getAttribute('data-desc');

            // Set titles and styles depending on type
            const titleEl = document.getElementById('edit_title');
            if (type === 'income') {
                titleEl.innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Edit Income Inflow`;
                titleEl.style.color = 'var(--color-success)';
                document.getElementById('edit_renter_wrapper').style.display = 'block';
                document.getElementById('edit_party_wrapper').style.display = 'none';
                document.getElementById('edit_renter').value = renter || '';
                document.getElementById('edit_party').value = '';
            } else {
                titleEl.innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Edit Expense Outflow`;
                titleEl.style.color = 'var(--color-danger)';
                document.getElementById('edit_renter_wrapper').style.display = 'none';
                document.getElementById('edit_party_wrapper').style.display = 'block';
                document.getElementById('edit_renter').value = '';
                document.getElementById('edit_party').value = party || '';
            }

            // Populate other common fields
            document.getElementById('edit_amount').value = amount;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_payment_method').value = method;
            document.getElementById('edit_banker_name').value = banker || '';
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_description').value = desc || '';

            // Update action path
            document.getElementById('editTransactionForm').action = `/transactions/${id}`;

            // Adjust banker field visibility
            toggleBankerField('edit');

            // Open Modal
            openModal('editTransactionModal');
        }
    </script>
@endsection
