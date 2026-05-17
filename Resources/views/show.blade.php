@extends('layouts.app')

@section('content')


{{-- Company Info --}}
<table class="table table-bordered">
    <tr>
        <th width="150"></th>
        <td></td>
    </tr>
  
</table>

<a href="{{ route('companies.index') }}" class="btn btn-secondary mt-3">Tilbage</a>

@endsection
