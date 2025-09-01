<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Mail\LeaveRequestStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class LeaveRequestService
{
    public function createLeaveRequest(User $user, array $data): LeaveRequest
    {
        if (!$user->canCreateLeaveRequests()) {
            throw new Exception('User cannot create leave requests');
        }

        // Check for overlapping requests (any status)
        $overlappingRequests = LeaveRequest::where('user_id', $user->id)
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('start_date', '<=', $data['start_date'])
                      ->where('end_date', '>=', $data['start_date']);
                })->orWhere(function ($q) use ($data) {
                    $q->where('start_date', '<=', $data['end_date'])
                      ->where('end_date', '>=', $data['end_date']);
                })->orWhere(function ($q) use ($data) {
                    $q->where('start_date', '>=', $data['start_date'])
                      ->where('end_date', '<=', $data['end_date']);
                });
            })
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->first();

        if ($overlappingRequests) {
            if ($overlappingRequests->status === LeaveRequest::STATUS_PENDING) {
                throw new Exception('Hai già una richiesta in attesa per questo periodo (' . 
                    $overlappingRequests->start_date->format('d/m/Y') . ' - ' . 
                    $overlappingRequests->end_date->format('d/m/Y') . ')');
            } else {
                throw new Exception('Hai già una richiesta approvata per questo periodo (' . 
                    $overlappingRequests->start_date->format('d/m/Y') . ' - ' . 
                    $overlappingRequests->end_date->format('d/m/Y') . ')');
            }
        }

        // Determine approver
        $approverId = $this->determineApprover($user);
        if (!$approverId) {
            throw new Exception('No approver found for this user');
        }

        return DB::transaction(function () use ($user, $data, $approverId) {
            $leaveRequest = LeaveRequest::create([
                'user_id' => $user->id,
                'manager_id' => $user->manager_id,
                'approver_id' => $approverId,
                'type' => $data['type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days_count' => $data['days_count'],
                'reason' => $data['reason'],
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            // Send notification email to approver
            $approver = User::find($approverId);
            if ($approver && $approver->email) {
                try {
                    Log::info('Invio notifica nuova richiesta a: ' . $approver->email);
                    Mail::to($approver->email)
                        ->send(new \App\Mail\NewLeaveRequestNotification($leaveRequest, $approver));
                } catch (\Exception $e) {
                    Log::error('Errore invio notifica nuova richiesta: ' . $e->getMessage());
                    // Non bloccare la creazione della richiesta per errori email
                }
            }

            return $leaveRequest;
        });
    }

    public function approveLeaveRequest(LeaveRequest $request, User $approver, string $notes = null): LeaveRequest
    {
        if (!$request->canBeApprovedBy($approver)) {
            throw new Exception('User cannot approve this leave request');
        }

        if ($request->status !== LeaveRequest::STATUS_PENDING) {
            throw new Exception('Only pending requests can be approved');
        }

        // Final overlap check before approval
        if ($request->hasOverlapWith($request->start_date, $request->end_date, $request->id)) {
            throw new Exception('Leave request overlaps with existing approved leave');
        }

        return DB::transaction(function () use ($request, $approver, $notes) {
            $request->update([
                'status' => LeaveRequest::STATUS_APPROVED,
                'approver_id' => $approver->id,
                'manager_notes' => $notes,
                'approved_at' => now(),
            ]);

            $updatedRequest = $request->fresh();
            
            // Send email notification
            try {
                Mail::to($updatedRequest->user->email)
                ->send(new LeaveRequestStatusUpdated(
                    $updatedRequest,
                    'Approvata',
                    $approver->name,
                    $notes
                ));
            } catch (\Exception $e) { 
                throw $e; // Rilancia l'eccezione per vedere l'errore
            }

            return $updatedRequest;
        });
    }

    public function rejectLeaveRequest(LeaveRequest $request, User $approver, string $notes): LeaveRequest
    {
        if (!$request->canBeApprovedBy($approver)) {
            throw new Exception('User cannot reject this leave request');
        }

        if ($request->status !== LeaveRequest::STATUS_PENDING) {
            throw new Exception('Only pending requests can be rejected');
        }

        return DB::transaction(function () use ($request, $approver, $notes) {
            $request->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'approver_id' => $approver->id,
                'manager_notes' => $notes,
                'rejected_at' => now(),
            ]);

            $updatedRequest = $request->fresh();
            
            // Send email notification
            Mail::to($updatedRequest->user->email)
                ->send(new LeaveRequestStatusUpdated(
                    $updatedRequest,
                    'Rifiutata',
                    $approver->name,
                    $notes
                ));

            return $updatedRequest;
        });
    }

    public function cancelLeaveRequest(LeaveRequest $request, User $user, string $reason = null): LeaveRequest
    {
        if (!$request->canBeCancelledBy($user)) {
            throw new Exception('User cannot cancel this leave request');
        }

        return DB::transaction(function () use ($request, $user, $reason) {
            $request->update([
                'status' => LeaveRequest::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            return $request->fresh();
        });
    }

    public function createOnBehalfOf(User $creator, User $targetUser, array $data): LeaveRequest
    {
        if (!$creator->isHR()) {
            throw new Exception('Only HR can create leave requests on behalf of others');
        }

        if (!$targetUser->canCreateLeaveRequests()) {
            throw new Exception('Target user cannot have leave requests created');
        }

        // Check for overlapping requests (any status)
        $overlappingRequests = LeaveRequest::where('user_id', $targetUser->id)
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('start_date', '<=', $data['start_date'])
                      ->where('end_date', '>=', $data['start_date']);
                })->orWhere(function ($q) use ($data) {
                    $q->where('start_date', '<=', $data['end_date'])
                      ->where('end_date', '>=', $data['end_date']);
                })->orWhere(function ($q) use ($data) {
                    $q->where('start_date', '>=', $data['start_date'])
                      ->where('end_date', '<=', $data['end_date']);
                });
            })
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->first();

        if ($overlappingRequests) {
            if ($overlappingRequests->status === LeaveRequest::STATUS_PENDING) {
                throw new Exception($targetUser->name . ' ha già una richiesta in attesa per questo periodo (' . 
                    $overlappingRequests->start_date->format('d/m/Y') . ' - ' . 
                    $overlappingRequests->end_date->format('d/m/Y') . ')');
            } else {
                throw new Exception($targetUser->name . ' ha già una richiesta approvata per questo periodo (' . 
                    $overlappingRequests->start_date->format('d/m/Y') . ' - ' . 
                    $overlappingRequests->end_date->format('d/m/Y') . ')');
            }
        }

        // Determine approver for target user
        $approverId = $this->determineApprover($targetUser);
        if (!$approverId) {
            throw new Exception('No approver found for target user');
        }

        return DB::transaction(function () use ($targetUser, $data, $approverId) {
            $leaveRequest = LeaveRequest::create([
                'user_id' => $targetUser->id,
                'manager_id' => $targetUser->manager_id,
                'approver_id' => $approverId,
                'type' => $data['type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days_count' => $data['days_count'],
                'reason' => $data['reason'],
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            // Send notification email to approver
            $approver = User::find($approverId);
            if ($approver && $approver->email) {
                try {
                    Mail::to($approver->email)
                        ->send(new \App\Mail\NewLeaveRequestNotification($leaveRequest, $approver));
                } catch (\Exception $e) {
                    // Non bloccare la creazione della richiesta per errori email
                }
            }

            return $leaveRequest;
        });
    }

    private function determineApprover(User $user): ?int
    {
        // HR requests need approval from another HR
        if ($user->isHR()) {
            $otherHR = User::where('role', User::ROLE_HR)
                ->where('id', '!=', $user->id)
                ->first();
            return $otherHR?->id;
        }

        // Manager requests need HR approval
        if ($user->isManager()) {
            $hr = User::where('role', User::ROLE_HR)->first();
            return $hr?->id;
        }

        // Employee requests need manager approval, fallback to HR
        if ($user->manager_id) {
            return $user->manager_id;
        }

        // Fallback to HR if no manager
        $hr = User::where('role', User::ROLE_HR)->first();
        return $hr?->id;
    }

    public function getLeaveRequestsForUser(User $user)
    {
        if ($user->isAdminIT()) {
            // Admin IT sees all requests (read-only)
            return LeaveRequest::with(['user', 'approver', 'user.organizationalUnit'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($user->isHR()) {
            // HR sees all requests
            return LeaveRequest::with(['user', 'approver', 'user.organizationalUnit'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($user->isManager()) {
            // Manager sees their own requests and their subordinates' requests
            return LeaveRequest::with(['user', 'approver', 'user.organizationalUnit'])
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('approver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Employee sees only their own requests
        return LeaveRequest::with(['user', 'approver'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingRequestsForApprover(User $user)
    {
        if (!$user->canApproveLeaveRequests()) {
            return collect();
        }

        return LeaveRequest::with(['user', 'user.organizationalUnit'])
            ->where('approver_id', $user->id)
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getFilteredLeaveRequestsForAdmin($request)
    {
        $query = LeaveRequest::with(['user', 'approver', 'user.organizationalUnit'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        if ($request->filled('department')) {
            $query->whereHas('user.organizationalUnit', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->department . '%');
            });
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        return $query->paginate(15)->withQueryString();
    }
}
