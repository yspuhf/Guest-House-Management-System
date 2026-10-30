<?php

namespace App\Http\Controllers;

use App\Models\ExpenditureHead;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenditureHeadController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $expenditureHeads = ExpenditureHead::withCount('bookings')->get();
        return view('masters.expenditure_heads.index', compact('expenditureHeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:expenditure_heads,code',
            'name' => 'required|string|max:150',
            'department' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'requires_approval_document' => 'required|boolean',
        ]);

        $head = ExpenditureHead::create($validated);
        $this->auditService->log(Auth::user(), 'Created Expenditure Head', 'Finance', ExpenditureHead::class, $head->id, null, $head->toArray());

        return back()->with('success', "Expenditure Head '{$head->name}' created.");
    }

    public function update(Request $request, ExpenditureHead $expenditureHead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'department' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'requires_approval_document' => 'required|boolean',
            'is_active' => 'required|boolean',
        ]);

        $before = $expenditureHead->toArray();
        $expenditureHead->update($validated);
        $this->auditService->log(Auth::user(), 'Updated Expenditure Head', 'Finance', ExpenditureHead::class, $expenditureHead->id, $before, $expenditureHead->toArray());

        return back()->with('success', "Expenditure Head '{$expenditureHead->name}' updated.");
    }
}

