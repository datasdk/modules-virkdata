@extends('layouts.app')


@section('actions')

<a href="{{ route("companies.create") }}" class="btn btn-primary">Opret firma</a>

@endsection

@section('content')


<livewire:table 
    :config="Modules\Companies\Tables\CompanyTable::class" 
/>


@endsection