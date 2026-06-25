<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::orderBy('name', 'asc')->get();
        return view('banks.index', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:banks,name',
        ]);

        Bank::create($validated);

        return redirect()->route('banks.index')->with('success', 'Bank profile created successfully.');
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:banks,name,' . $bank->id,
        ]);

        $bank->update($validated);

        return redirect()->route('banks.index')->with('success', 'Bank details updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('banks.index')->with('success', 'Bank profile deleted successfully.');
    }
}
