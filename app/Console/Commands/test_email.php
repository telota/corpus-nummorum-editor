<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\standardMail;

class test_email extends Command {    
    protected $signature = 'test:email';
    protected $description = 'Send a test email';
    public function __construct() { parent::__construct(); }
    // ------------------------------------------------------------------------------
    
    public function handle() {
        // Script
        date_default_timezone_set('Europe/Berlin');
        $date = date('d.m.Y, H:i:s');

        $content = ['test1', 'test2'];        

        // Send Mail
        if (!empty($content[0])) {
            // Send Mails
            Mail::to('jan.koester@bbaw.de') -> send(
                new standardMail([
                    'date' => $date,
                    'content' => $content,
                    'recipient_name' => 'Jan'
                ], [
                    'subject' => 'test'
                ])
            );
        }
    }
}
