@extends('layouts.app')

@section('page_title', 'Hisab-Kitab Dashboard')

@section('header_actions')
    <button class="btn btn-secondary btn-sm" onclick="openModal('openingBalancesModal')">
        <i class="fa-solid fa-scale-unbalanced-flip"></i> Set Opening Balances
    </button>
    <a href="{{ route('transactions.index') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus"></i> Add Transaction
    </a>
@endsection

@section('content')
    <!-- Quick Statistics Grid -->
    <div class="card-grid">
        <!-- Net Profit Card -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Net Profit</span>
                <span class="stat-value" style="color: {{ $netProfit >= 0 ? 'var(--color-success)' : 'var(--color-danger)' }}">
                    ₹{{ number_format($netProfit, 2) }}
                </span>
            </div>
            <div class="stat-icon profit" style="background-color: {{ $netProfit >= 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $netProfit >= 0 ? 'var(--color-success)' : 'var(--color-danger)' }}">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <!-- Total Expenses Card -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Total Expenses</span>
                <span class="stat-value">₹{{ number_format($totalExpense, 2) }}</span>
            </div>
            <div class="stat-icon expense">
                <i class="fa-solid fa-arrow-trend-down"></i>
            </div>
        </div>

        <!-- Security Deposits Held Card -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Deposits Held</span>
                <span class="stat-value" style="color: #fbbf24;">₹{{ number_format($securityDepositsHeld, 2) }}</span>
            </div>
            <div class="stat-icon warning" style="background-color: rgba(245, 158, 11, 0.1); color: #fbbf24;">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <!-- Cash Account Card -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Cash Balance</span>
                <span class="stat-value" style="color: {{ $cashBalance >= 0 ? 'var(--color-warning)' : 'var(--color-danger)' }}">
                    ₹{{ number_format($cashBalance, 2) }}
                </span>
            </div>
            <div class="stat-icon warning">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>

        <!-- Bank Account Card -->
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Bank Balance</span>
                <span class="stat-value" style="color: {{ $bankBalance >= 0 ? 'var(--color-info)' : 'var(--color-danger)' }}">
                    ₹{{ number_format($bankBalance, 2) }}
                </span>
            </div>
            <div class="stat-icon" style="background-color: rgba(6, 182, 212, 0.1); color: var(--color-info);">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>
    </div>

    <!-- Charts & Recents Grid -->
    <div class="dashboard-grid">
        <!-- Recent Transactions -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-clock-rotate-left" style="color: var(--color-warning);"></i>
                    Recent Transactions
                </h2>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">View All</a>
            </div>
            
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">{{ $transaction->date->format('M d, Y') }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $transaction->category }}</div>
                                </td>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="badge badge-income" style="margin-bottom: 2px;">In</span>
                                    @else
                                        <span class="badge badge-expense" style="margin-bottom: 2px;">Out</span>
                                    @endif
                                    
                                    <div style="font-size: 0.85rem; font-weight: 600;">
                                        @if($transaction->type === 'income')
                                            {{ $transaction->renter ? $transaction->renter->name : 'Other Income' }}
                                        @else
                                            {{ $transaction->expenseParty ? $transaction->expenseParty->name : 'Other Expense' }}
                                        @endif
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        @if($transaction->payment_method === 'bank_transfer')
                                            <i class="fa-solid fa-university" style="font-size: 0.7rem;"></i> Bank 
                                            @if($transaction->banker_name)
                                                ({{ $transaction->banker_name }})
                                            @endif
                                        @else
                                            <i class="fa-solid fa-money-bill" style="font-size: 0.7rem;"></i> Cash
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: {{ $transaction->type === 'income' ? 'var(--color-success)' : 'var(--color-danger)' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }}₹{{ number_format($transaction->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                                    <i class="fa-regular fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                                    No transaction records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Opening Balances Modal -->
    <div class="modal-overlay" id="openingBalancesModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="card-title" style="color: var(--color-primary);"><i class="fa-solid fa-gears"></i> Configure Opening Balances</h3>
                <button class="close-modal" onclick="closeModal('openingBalancesModal')">&times;</button>
            </div>
            <form action="{{ route('dashboard.update-opening-balances') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom: 1.25rem; font-size: 0.9rem; color: var(--text-secondary);">
                        Set the starting cash and bank balances for your bookkeeping book.
                    </p>
                    
                    <div class="form-group">
                        <label for="setup_opening_cash">Opening Cash Balance (₹)</label>
                        <input type="number" step="0.01" name="opening_cash" id="setup_opening_cash" class="form-control" value="{{ $openingCash }}" required>
                    </div>

                    <h5 style="color: var(--color-info); margin-top: 1.5rem; margin-bottom: 0.75rem; font-family: var(--font-heading); font-size: 0.95rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                        <i class="fa-solid fa-university"></i> Bank Account Starting Balances
                    </h5>

                    @forelse($banksList as $b)
                        <div class="form-group">
                            <label for="setup_bank_{{ $b->id }}">{{ $b->name }} Starting Balance (₹)</label>
                            <input type="number" step="0.01" name="banks[{{ $b->id }}]" id="setup_bank_{{ $b->id }}" class="form-control" value="{{ $b->opening_balance }}" required>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 1rem 0;">
                            <i class="fa-solid fa-building-columns" style="font-size: 1.5rem; margin-bottom: 0.35rem; display: block;"></i>
                            No Banker accounts registered. Set them up under the <strong>Bankers List</strong> tab.
                        </div>
                    @endforelse
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('openingBalancesModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Balances</button>
                </div>
            </form>
        </div>
    </div>
@endsection
