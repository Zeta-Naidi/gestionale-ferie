<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ManagerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->canApproveLeaveRequests()) {
            abort(403, 'Accesso negato. Solo i manager e HR possono accedere a questa sezione.');
        }

        // Se è un manager, mostra le richieste gestite
        // Se è HR, mostra TUTTE le richieste tranne le sue
        if ($user->isManager() && !$user->isHR()) {
            $pendingRequests = $user->managedLeaveRequests()
                ->with('user')
                ->pending()
                ->orderBy('created_at', 'desc')
                ->get();

            $recentApprovals = $user->managedLeaveRequests()
                ->with('user')
                ->whereIn('status', ['approved', 'rejected'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            // Per HR: mostra TUTTE le richieste tranne le sue
            $pendingRequests = LeaveRequest::with('user')
                ->where('user_id', '!=', $user->id)
                ->pending()
                ->orderBy('created_at', 'desc')
                ->get();

            $recentApprovals = LeaveRequest::with('user')
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', ['approved', 'rejected'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        }

        return Inertia::render('Manager/Dashboard', [
            'pendingRequests' => $pendingRequests,
            'recentApprovals' => $recentApprovals,
            'pendingApprovalsCount' => $pendingRequests->count(),
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        if (!$leaveRequest->canBeApprovedBy($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'manager_notes' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'manager_notes' => $validated['manager_notes'] ?? null,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Richiesta approvata con successo!');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        if (!$leaveRequest->canBeApprovedBy($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'manager_notes' => 'required|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'manager_notes' => $validated['manager_notes'],
            'rejected_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Richiesta rifiutata.');
    }
}
