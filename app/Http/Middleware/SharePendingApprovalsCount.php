<?php

namespace App\Http\Middleware;

use App\Services\LeaveRequestService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SharePendingApprovalsCount
{
    protected $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $pendingApprovals = $this->leaveRequestService->getPendingRequestsForApprover($user);
            
            Inertia::share('pendingApprovalsCount', $pendingApprovals->count());
        }

        return $next($request);
    }
}
