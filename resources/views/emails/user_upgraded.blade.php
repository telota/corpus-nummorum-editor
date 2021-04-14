@extends('emails.generic_layout')

@section('greeting')
    Dear{{ empty($data['firstname']) || empty($data['lastname']) ? (empty($data['name']) ? '' : (' '.$data['name'])) : (' '.$data['firstname'].' '.$data['lastname']) }}.
@endsection

@section('body')
    Your registration has been accepted. You were granted access to the <a href="https://data.corpus-nummorum.eu/editor#">CN Editor</a>.<br/>
    We look forward to your contributions. Please do not hesitate to contact us if you have any questions.
@endsection