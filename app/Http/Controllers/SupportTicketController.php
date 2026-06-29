<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        SupportTicket::create($data + [
            'user_id' => $request->user()?->id,
            'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
        ]);

        return back()->with('status', 'Support ticket submitted.');
    }
}
