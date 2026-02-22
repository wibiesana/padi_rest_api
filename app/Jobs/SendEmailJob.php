<?php

namespace App\Jobs;

use Wibiesana\Padi\Core\Logger;
use Wibiesana\Padi\Core\Email;

class SendEmailJob
{
    /**
     * Handle the job process.
     * The Queue worker will call this method with the provided data.
     */
    public function handle(array $data): void
    {
        $to = $data['email'];
        $subject = $data['subject'];
        $body = $data['body'];

        Logger::info("Job SendEmailJob started for: " . $to);

        // Simulation of sending email
        Email::send($to, $subject, $body);

        // For demonstration, we'll just sleep a bit
        sleep(2);

        Logger::info("Job SendEmailJob completed for: " . $to);
    }
}
