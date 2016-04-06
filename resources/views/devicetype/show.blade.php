@extends('layouts.master')

@section('content')

    <h1>Devicetype</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID.</th> <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $devicetype->id }}</td> <td> {{ $devicetype->name }} </td>
                </tr>
            </tbody>    
        </table>
    </div>

@endsection