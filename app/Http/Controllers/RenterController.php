<?php

namespace App\Http\Controllers;

use App\Models\Renter;
use App\Models\Transaction;
use App\Models\Bank;
use Illuminate\Http\Request;

class RenterController extends Controller
{
    public function index()
    {
        // Get renters and calculate their total paid rent amount
        $renters = Renter::withSum(['transactions' => function ($query) {
            $query->where('type', 'income')->where('category', 'Rent');
        }], 'amount')->orderBy('name', 'asc')->get();
        
        $banks = Bank::orderBy('name', 'asc')->get();

        return view('renters.index', compact('renters', 'banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rent_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_payment_method' => 'required|in:cash,bank_transfer',
            'deposit_banker_name' => 'nullable|string|max:255',
        ]);

        // Clean up banker names if payment is cash
        if ($validated['payment_method'] === 'cash') {
            $validated['banker_name'] = null;
        }
        if ($validated['deposit_payment_method'] === 'cash') {
            $validated['deposit_banker_name'] = null;
        }

        $renter = Renter::create($validated);

        // Auto-record the deposit as an Income entry if amount > 0
        if ($renter->deposit_amount > 0) {
            Transaction::create([
                'type' => 'income',
                'amount' => $renter->deposit_amount,
                'payment_method' => $renter->deposit_payment_method,
                'banker_name' => $renter->deposit_payment_method === 'bank_transfer' ? $renter->deposit_banker_name : null,
                'date' => now()->format('Y-m-d'),
                'renter_id' => $renter->id,
                'category' => 'Security Deposit',
                'description' => "Advance security deposit received from " . $renter->name,
            ]);
        }

        return redirect()->route('renters.index')->with('success', 'Renter profile created successfully.' . ($renter->deposit_amount > 0 ? ' Advance deposit logged as Income.' : ''));
    }

    public function update(Request $request, Renter $renter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rent_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_payment_method' => 'required|in:cash,bank_transfer',
            'deposit_banker_name' => 'nullable|string|max:255',
        ]);

        if ($validated['payment_method'] === 'cash') {
            $validated['banker_name'] = null;
        }
        if ($validated['deposit_payment_method'] === 'cash') {
            $validated['deposit_banker_name'] = null;
        }

        $oldDeposit = $renter->deposit_amount;
        $renter->update($validated);

        // Sync changes with transactions
        if ($renter->deposit_amount > $oldDeposit) {
            // Log difference as Income
            $diff = $renter->deposit_amount - $oldDeposit;
            Transaction::create([
                'type' => 'income',
                'amount' => $diff,
                'payment_method' => $renter->deposit_payment_method,
                'banker_name' => $renter->deposit_payment_method === 'bank_transfer' ? $renter->deposit_banker_name : null,
                'date' => now()->format('Y-m-d'),
                'renter_id' => $renter->id,
                'category' => 'Security Deposit',
                'description' => "Security deposit increase received from " . $renter->name,
            ]);
        } elseif ($renter->deposit_amount < $oldDeposit) {
            // Log difference as Expense (Refund)
            $diff = $oldDeposit - $renter->deposit_amount;
            Transaction::create([
                'type' => 'expense',
                'amount' => $diff,
                'payment_method' => $renter->deposit_payment_method,
                'banker_name' => $renter->deposit_payment_method === 'bank_transfer' ? $renter->deposit_banker_name : null,
                'date' => now()->format('Y-m-d'),
                'renter_id' => $renter->id,
                'category' => 'Deposit Refund',
                'description' => "Security deposit decrease returned to " . $renter->name,
            ]);
        }

        return redirect()->route('renters.index')->with('success', 'Renter details updated successfully.');
    }

    public function destroy(Renter $renter)
    {
        $renter->delete();
        return redirect()->route('renters.index')->with('success', 'Renter profile deleted successfully.');
    }

    /**
     * Record a rent payment transaction (creates an Income entry)
     */
    public function recordPayment(Request $request, Renter $renter)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'months' => 'required|array|min:1',
            'description' => 'nullable|string',
        ]);

        $monthsStr = implode(', ', $validated['months']);

        Transaction::create([
            'type' => 'income',
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'banker_name' => $validated['payment_method'] === 'bank_transfer' ? $validated['banker_name'] : null,
            'date' => $validated['date'],
            'renter_id' => $renter->id,
            'category' => 'Rent',
            'description' => $validated['description'] ?? "Rent payment received from " . $renter->name . " for: " . $monthsStr,
        ]);

        return redirect()->route('renters.index')->with('success', 'Rent payment logged as Income.');
    }

    /**
     * Refund/Return security deposit (creates an Expense entry and reduces renter deposit to 0)
     */
    public function refundDeposit(Request $request, Renter $renter)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0|max:' . ($renter->deposit_amount + 0.05),
            'payment_method' => 'required|in:cash,bank_transfer',
            'banker_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create Expense entry
        Transaction::create([
            'type' => 'expense',
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'banker_name' => $validated['payment_method'] === 'bank_transfer' ? $validated['banker_name'] : null,
            'date' => $validated['date'],
            'renter_id' => $renter->id,
            'category' => 'Deposit Refund',
            'description' => $validated['description'] ?? "Security deposit refunded to " . $renter->name,
        ]);

        // Reduce the renter's deposit amount in the database
        $newDeposit = max(0, $renter->deposit_amount - $validated['amount']);
        $renter->update([
            'deposit_amount' => $newDeposit
        ]);

        return redirect()->route('renters.index')->with('success', 'Security deposit refund logged as Expense. Renter deposit balance updated.');
    }
}
