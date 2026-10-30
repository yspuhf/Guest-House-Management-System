<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $taxes = Tax::all();
        return view('masters.taxes.index', compact('taxes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:taxes,code',
            'calculation_method' => 'required|in:percentage,fixed',
            'rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $tax = Tax::create($validated);
        $this->auditService->log(Auth::user(), 'Created Tax Master', 'Finance', Tax::class, $tax->id, null, $tax->toArray());

        return back()->with('success', "Tax '{$tax->name}' created.");
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $before = $tax->toArray();
        $tax->update($validated);
        $this->auditService->log(Auth::user(), 'Updated Tax Master', 'Finance', Tax::class, $tax->id, $before, $tax->toArray());

        return back()->with('success', "Tax '{$tax->name}' updated.");
    }
}

