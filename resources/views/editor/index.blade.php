@extends('editor.layout')

@section('app')

<div>EDITOR</div>

<div> {!! json_encode($data) !!} </div>

@endsection
