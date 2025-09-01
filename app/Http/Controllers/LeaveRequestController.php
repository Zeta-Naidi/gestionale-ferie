<?php

namespace App\Http\Controllers;

use App\Http\ValidationPatterns;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $leaveRequests = $this->leaveRequestService->getLeaveRequestsForUser($user);
        $pendingApprovals = $this->leaveRequestService->getPendingRequestsForApprover($user);

        // For Admin IT, get paginated and filtered results
        $adminLeaveRequests = null;
        if ($user->isAdminIT()) {
            $adminLeaveRequests = $this->leaveRequestService->getFilteredLeaveRequestsForAdmin($request);
        }

        return Inertia::render('Dashboard', [
            'leaveRequests' => $leaveRequests,
            'pendingApprovals' => $pendingApprovals,
            'adminLeaveRequests' => $adminLeaveRequests,
            'user' => $user->load(['manager', 'organizationalUnit']),
            'userPermissions' => [
                'canCreateRequests' => $user->canCreateLeaveRequests(),
                'canApproveRequests' => $user->canApproveLeaveRequests(),
                'isHR' => $user->isHR(),
                'isManager' => $user->isManager(),
                'isAdminIT' => $user->isAdminIT(),
            ],
            'filters' => $request->only(['status', 'type', 'user_name', 'department', 'start_date', 'end_date']),
            'pendingApprovalsCount' => $pendingApprovals->count()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:vacation,personal',
            'start_date' => ValidationPatterns::DATE_AFTER_OR_EQUAL_TODAY,
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => ValidationPatterns::OPTIONAL_STRING_1000,
            'user_id' => ValidationPatterns::OPTIONAL_EXISTING_USER_ID,
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        $user = Auth::user();
        $validated['days_count'] = $daysCount;

        try {
            if (isset($validated['user_id']) && $user->isHR()) {
                // HR creating on behalf of another user
                $targetUser = User::findOrFail($validated['user_id']);
                $leaveRequest = $this->leaveRequestService->createOnBehalfOf($user, $targetUser, $validated);
            } else {
                // User creating their own request
                $leaveRequest = $this->leaveRequestService->createLeaveRequest($user, $validated);
            }

            return redirect()->back()->with('success', 'Richiesta di ferie inviata con successo!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(LeaveRequest $leaveRequest, Request $request)
    {
        $validated = $request->validate([
            'notes' => ValidationPatterns::OPTIONAL_STRING_1000,
        ]);

        try {
            $this->leaveRequestService->approveLeaveRequest(
                $leaveRequest, 
                Auth::user(), 
                $validated['notes'] ?? null
            );

            return redirect()->back()->with('success', 'Richiesta approvata con successo!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(LeaveRequest $leaveRequest, Request $request)
    {
        $validated = $request->validate([
            'notes' => ValidationPatterns::STRING_1000,
        ]);

        try {
            $this->leaveRequestService->rejectLeaveRequest(
                $leaveRequest, 
                Auth::user(), 
                $validated['notes']
            );

            return redirect()->back()->with('success', 'Richiesta rifiutata.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(LeaveRequest $leaveRequest, Request $request)
    {
        $validated = $request->validate([
            'reason' => ValidationPatterns::OPTIONAL_STRING_1000,
        ]);

        try {
            $this->leaveRequestService->cancelLeaveRequest(
                $leaveRequest, 
                Auth::user(), 
                $validated['reason'] ?? null
            );

            return redirect()->back()->with('success', 'Richiesta cancellata.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        try {
            $this->leaveRequestService->cancelLeaveRequest($leaveRequest, Auth::user());
            return redirect()->back()->with('success', 'Richiesta eliminata con successo!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getUsers()
    {
        $user = Auth::user();
        
        if (!$user->isHR()) {
            abort(403);
        }

        $users = User::where('role', '!=', User::ROLE_ADMIN_IT)
            ->with('organizationalUnit')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }
}
