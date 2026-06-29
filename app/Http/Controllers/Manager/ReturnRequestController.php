<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function index()
    {
        return view('manager.returns.index', [
            'returns' => ReturnRequest::with(['order', 'user', 'product'])->latest()->paginate(12),
        ]);
    }

    public function update(Request $request, ReturnRequest $return)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,return_initiated,refunded'],
            'manager_reason' => ['required', 'string', 'max:1000'],
        ]);

        $return->update($data + ['resolved_at' => now()]);

        return back()->with('status', 'Return request updated.');
    }
}
