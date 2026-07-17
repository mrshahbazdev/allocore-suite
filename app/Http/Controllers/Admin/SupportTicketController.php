<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['user', 'team', 'assignee'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('subject', 'like', '%'.$request->search.'%')
                    ->orWhere('body', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'team', 'assignee', 'messages.user']);
        $agents = User::role('admin')->orWhereHas('roles', fn ($q) => $q->where('name', 'support'))->orderBy('name')->get();

        return view('admin.support-tickets.show', compact('supportTicket', 'agents'));
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', SupportTicket::STATUSES),
            'priority' => 'required|in:'.implode(',', SupportTicket::PRIORITIES),
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:255',
        ]);

        $validated['closed_at'] = $validated['status'] === 'closed' ? now() : null;

        $supportTicket->update($validated);

        return redirect()->route('admin.support-tickets.show', $supportTicket)->with('success', __('admin.support_tickets.updated'));
    }

    public function storeMessage(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        if (! in_array($supportTicket->status, ['in_progress', 'closed'])) {
            $supportTicket->update(['status' => 'in_progress']);
        }

        return redirect()->route('admin.support-tickets.show', $supportTicket)->with('success', __('admin.support_tickets.message_added'));
    }

    public function destroy(SupportTicket $supportTicket)
    {
        $supportTicket->delete();

        return redirect()->route('admin.support-tickets.index')->with('success', __('admin.support_tickets.deleted'));
    }
}
