<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $status;
    public $managerName;
    public $notes;

    /**
     * Create a new message instance.
     *
     * @param LeaveRequest $leaveRequest
     * @param string $status
     * @param string $managerName
     * @param string|null $notes
     * @return void
     */
    public function __construct(LeaveRequest $leaveRequest, string $status, string $managerName, ?string $notes = null)
    {
        $this->leaveRequest = $leaveRequest;
        $this->status = $status;
        $this->managerName = $managerName;
        $this->notes = $notes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Stato della tua richiesta di ferie: " . 
                  ucfirst(strtolower($this->status)) . " - " . 
                  $this->leaveRequest->start_date->format('d/m/Y') . " - " . 
                  $this->leaveRequest->end_date->format('d/m/Y');

        return $this->subject($subject)
                    ->view('emails.leave-request-status');
    }
}
