@extends('emails.generic_layout')

@section('greeting')
    Guten Tag liebe CN-Admins,
@endsection

@section('body')
    <b>{{ empty($data['firstname']) || empty($data['lastname']) ? (empty($data['name']) ? 'ein unbekannter User' : $data['name']) : ($data['firstname'].' '.$data['lastname']) }}</b>
    hat sich für den CN Editor registriert.<br/>
    Nutzen Sie bitte die <a href="https://data.corpus-nummorum.eu/editor#/users/new">Userverwaltung</a>, um neue Users freizuschalten oder abzulehnen.
@endsection