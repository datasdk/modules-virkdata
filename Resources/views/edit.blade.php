@extends('layouts.app')

@section('content')



<form method="POST" action="{{ route('companies.update', $company->id) }}">
    @csrf
    @method('PATCH')

    {{-- Company Form Fields --}}
    @livewire('companies::company-form-fields', [
        'company' => $company
    ])




    <button type="submit" class="btn btn-primary">Opdater Firma</button>
    <a href="{{ route('companies.index') }}" class="btn btn-secondary">Tilbage</a>

</form>

@endsection
