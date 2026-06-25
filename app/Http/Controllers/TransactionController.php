<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Renter;
use App\Models\ExpenseParty;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['renter', 'expenseParty']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', Carbon::parse($request->month)->month)
                  ->whereYear('date', Carbon::parse($request->month)->year);
        }

        $transactions = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // Fetch dynamic lists for forms
        $renters = Renter::orderBy('name', 'asc')->get();
        $expenseParties = ExpenseParty::orderBy('name', 'asc')->get();
        $banks = Bank::orderBy('name', 'asc')->get();

        // Unique categories for filtering
        $categories = Transaction::select('category')->distinct()->pluck('category')->toArray();

        return view('transactions.index', compact('transactions', 'renters', 'expenseParties', 'categories', 'banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'renter_id' => 'nullable|exists:renters,id',
            'expense_party_id' => 'nullable|exists:expense_parties,id',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Clean up banker name if payment is cash
        if ($validated['payment_method'] === 'cash') {
            $validated['banker_name'] = null;
        }

        // Clean up specific associations based on type
        if ($validated['type'] === 'income') {
            $validated['expense_party_id'] = null;
        } else {
            $validated['renter_id'] = null;
        }

        Transaction::create($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction entry recorded successfully.');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'renter_id' => 'nullable|exists:renters,id',
            'expense_party_id' => 'nullable|exists:expense_parties,id',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validated['payment_method'] === 'cash') {
            $validated['banker_name'] = null;
        }

        if ($transaction->type === 'income') {
            $validated['expense_party_id'] = null;
        } else {
            $validated['renter_id'] = null;
        }

        $transaction->update($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction entry deleted successfully.');
    }
}
