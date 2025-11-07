@extends('layouts.app')

@section('content')
<div id="pos-app" data-user='@json(auth()->user()->only(['id', 'name', 'email', 'role']))'></div>
@endsection
