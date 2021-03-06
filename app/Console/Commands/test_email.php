<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\standardMail;

class test_email extends Command {
    protected $signature = 'test:email {mail}';
    protected $description = 'Send a test email';
    public function __construct() { parent::__construct(); }
    // ------------------------------------------------------------------------------

    public function handle() {

        $mail = $this->argument('mail');

        if (!empty($mail)) {
            Mail::to($mail) -> send(
                new standardMail([
                    'date' => date('d.m.Y, H:i:s'),
                    'content' => ['Test-Email sent successfully'],
                    'recipient_name' => $mail
                ], [
                    'subject' => 'Test-Email'
                ])
            );
        }
    }
}
