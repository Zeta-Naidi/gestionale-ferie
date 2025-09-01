<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewLeaveRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $approver;

    /**
     * Create a new message instance.
     *
     * @param LeaveRequest $leaveRequest
     * @param User $approver
     * @return void
     */
    public function __construct(LeaveRequest $leaveRequest, User $approver)
    {
        $this->leaveRequest = $leaveRequest;
        $this->approver = $approver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Nuova richiesta di ferie da approvare - " . 
                  $this->leaveRequest->user->name . " - " . 
                  $this->leaveRequest->start_date->format('d/m/Y') . " - " . 
                  $this->leaveRequest->end_date->format('d/m/Y');

        return $this->subject($subject)
                    ->view('emails.new-leave-request-notification');
    }
}
