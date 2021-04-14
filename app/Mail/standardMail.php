<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use DB;

class standardMail extends Mailable {
    use Queueable, SerializesModels;

    public $m_from;
    public $m_subject;
    public $m_view;
    public $data;

    public function __construct($data = [], $meta = []) {
        // Meta
        $this->m_from       = empty($meta['from']) ? 'Corpus Nummorum' : $meta['from'];
        $this->m_subject    = $data['subject'] = empty($meta['subject']) ? '' : (' - '.trim($meta['subject']));
        $this->m_view       = empty($meta['view']) ? 'generic_message' : trim($meta['view']);

        // pick random type
        $dbi = json_decode(DB::table(config('dbi.tablenames.types'))
        -> select('id')
        -> where('publication_state', 1)
        -> OrderByRaw('RAND()')
        -> limit(1)
        -> get(), true);
        
        if (!empty($dbi[0])) { $data['random_type'] = $dbi[0]['id']; }

        $this->data = $data;
    }
    
    public function build() {
        return $this
        -> from('telotabot@bbaw.de', $this->m_from)          
        -> subject('CORPUS NUMMORUM'.$this->m_subject)
        -> view('emails.'.$this->m_view);
    }
}
