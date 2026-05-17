@extends('layouts.app')

@section('content')



<form method="POST" action="{{ route('companies.store') }}">
    @csrf

   

    <button type="submit" class="btn btn-primary">Opret Firma</button>
    <a href="{{ route('companies.index') }}" class="btn btn-secondary">Tilbage</a>

</form>

@endsection
