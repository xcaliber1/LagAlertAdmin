<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmergencyReported extends Mailable
{
    use Queueable, SerializesModels;

    public $emergencyData;

    /**
     * Create a new message instance.
     *
     * @param array $emergencyData
     * @return void
     */
    public function __construct($emergencyData)
    {
        $this->emergencyData = $emergencyData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('New Emergency Report')
                    ->view('emails.emergency_reported')
                    ->with('emergencyData', $this->emergencyData);
    }
}
