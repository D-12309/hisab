<?php

namespace App\Http\Controllers;

use App\Models\ExpenseParty;
use App\Models\Transaction;
use App\Models\Bank;
use Illuminate\Http\Request;

class ExpensePartyController extends Controller
{
    public function index()
    {
        // Get expense parties and calculate their total accumulated expenses
        $parties = ExpenseParty::withSum(['transactions' => function ($query) {
            $query->where('type', 'expense');
        }], 'amount')->orderBy('name', 'asc')->get();

        $banks = Bank::orderBy('name', 'asc')->get();

        return view('expense-parties.index', compact('parties', 'banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        ExpenseParty::create($validated);

        return redirect()->route('expense-parties.index')->with('success', 'Expense party created successfully.');
    }

    public function update(Request $request, ExpenseParty $expenseParty)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $expenseParty->update($validated);

        return redirect()->route('expense-parties.index')->with('success', 'Expense party details updated successfully.');
    }

    public function destroy(ExpenseParty $expenseParty)
    {
        $expenseParty->delete();
        return redirect()->route('expense-parties.index')->with('success', 'Expense party deleted successfully.');
    }

    /**
     * Record an expense transaction for this vendor party (creates an Expense entry)
     */
    public function recordExpense(Request $request, ExpenseParty $expenseParty)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'banker_name' => $validated['payment_method'] === 'bank_transfer' ? $validated['banker_name'] : null,
            'date' => $validated['date'],
            'expense_party_id' => $expenseParty->id,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? "Expense paid to " . $expenseParty->name,
        ]);

        return redirect()->route('expense-parties.index')->with('success', 'Expense logged successfully against ' . $expenseParty->name . '.');
    }
}
