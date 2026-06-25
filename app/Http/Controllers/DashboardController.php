<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Renter;
use App\Models\ExpenseParty;
use App\Models\Bank;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Opening Balances
        $openingCash = (float)Setting::get('opening_cash', 0.00);
        $openingBank = (float)Bank::sum('opening_balance');

        // Security deposit sum
        $securityDepositsHeld = (float)Renter::sum('deposit_amount');

        // 1. Core aggregates
        $totalIncome = (float)Transaction::where('type', 'income')->sum('amount');
        $totalExpense = (float)Transaction::where('type', 'expense')->sum('amount');
        
        // Net Profit = Opening Bank Balance + Cash Opening Balance + Total Deposits (Inflow) - Expense Amount
        $netProfit = $openingBank + $openingCash + $totalIncome - $totalExpense;

        // Cash balance (Opening Cash + Income Cash - Expense Cash)
        $cashIncome = Transaction::where('type', 'income')->where('payment_method', 'cash')->sum('amount');
        $cashExpense = Transaction::where('type', 'expense')->where('payment_method', 'cash')->sum('amount');
        $cashBalance = $openingCash + $cashIncome - $cashExpense;

        // Bank balance (Opening Bank + Income Bank - Expense Bank)
        $bankIncome = Transaction::where('type', 'income')->where('payment_method', 'bank_transfer')->sum('amount');
        $bankExpense = Transaction::where('type', 'expense')->where('payment_method', 'bank_transfer')->sum('amount');
        $bankBalance = $openingBank + $bankIncome - $bankExpense;

        // 2. Counts and lists
        $rentersCount = Renter::count();
        $expensePartiesCount = ExpenseParty::count();
        $transactionsCount = Transaction::count();
        $banksList = Bank::orderBy('name', 'asc')->get();

        // 3. Recent 8 transactions with eager loaded relationships
        $recentTransactions = Transaction::with(['renter', 'expenseParty'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        // 4. Monthly Trend Data (last 6 months) for Chart.js
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $monthlyData[$monthKey] = [
                'label' => $month->format('F Y'),
                'income' => 0,
                'expense' => 0
            ];
        }

        // Fetch transactions for the 6 month window
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $windowTransactions = Transaction::where('date', '>=', $startDate)->get();

        foreach ($windowTransactions as $transaction) {
            $key = Carbon::parse($transaction->date)->format('Y-m');
            if (isset($monthlyData[$key])) {
                if ($transaction->type === 'income') {
                    $monthlyData[$key]['income'] += (float)$transaction->amount;
                } else {
                    $monthlyData[$key]['expense'] += (float)$transaction->amount;
                }
            }
        }

        $chartLabels = [];
        $chartIncome = [];
        $chartExpense = [];
        foreach ($monthlyData as $data) {
            $chartLabels[] = $data['label'];
            $chartIncome[] = $data['income'];
            $chartExpense[] = $data['expense'];
        }

        return view('dashboard', compact(
            'totalIncome',
            'totalExpense',
            'netProfit',
            'securityDepositsHeld',
            'openingCash',
            'openingBank',
            'cashBalance',
            'bankBalance',
            'rentersCount',
            'expensePartiesCount',
            'transactionsCount',
            'recentTransactions',
            'chartLabels',
            'chartIncome',
            'chartExpense',
            'banksList'
        ));
    }

    /**
     * Save/Update Opening Cash and Bank balances
     */
    public function updateOpeningBalances(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'banks' => 'nullable|array',
            'banks.*' => 'required|numeric|min:0',
        ]);

        // Save opening cash
        Setting::set('opening_cash', $request->opening_cash);

        // Save opening bank balances
        if ($request->has('banks')) {
            foreach ($request->banks as $bankId => $balance) {
                $bank = Bank::find($bankId);
                if ($bank) {
                    $bank->update(['opening_balance' => $balance]);
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Opening balances updated successfully.');
    }
}
