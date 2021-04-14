@extends('emails.generic_layout')

@section('greeting')
    Guten Tag{{ empty($data['recipient_name']) ? '' : (' '.trim($data['recipient_name'])) }},
@endsection

@section('body')        
    {!! 
        empty($data['content']) ? 
        'upps, da ist wohl etwas schiefgelaufen. Eigentlich sollte hier eine Nachricht stehen...<br/>' : 
        (is_array($data['content']) ? trim(implode('<br/>', $data['content'])) : trim($data['content'])) 
    !!}
@endsection