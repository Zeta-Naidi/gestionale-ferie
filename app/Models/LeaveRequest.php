<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'approver_id',
        'type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'manager_notes',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    const TYPE_VACATION = 'vacation';
    const TYPE_PERSONAL = 'personal';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeFuture($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Workflow validation methods
    public function canBeApprovedBy(User $user): bool
    {
        // No self-approval
        if ($this->user_id === $user->id) {
            return false;
        }

        // HR can approve all requests except their own (needs other HR)
        if ($user->isHR()) {
            if ($this->user->isHR()) {
                // HR requesting leave - needs approval from another HR
                return $user->id !== $this->user_id;
            }
            return true;
        }

        // Manager can approve requests from their unit
        if ($user->isManager()) {
            return $this->user->manager_id === $user->id;
        }

        return false;
    }

    public function canBeCancelledBy(User $user): bool
    {
        // Owner can cancel pending requests
        if ($this->user_id === $user->id && $this->status === self::STATUS_PENDING) {
            return true;
        }

        // Approver or HR can cancel approved requests
        if ($this->status === self::STATUS_APPROVED) {
            return $user->isHR() || $this->approver_id === $user->id;
        }

        return false;
    }

    public function hasOverlapWith($startDate, $endDate, $excludeId = null): bool
    {
        $query = self::where('user_id', $this->user_id)
            ->where('status', self::STATUS_APPROVED)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
