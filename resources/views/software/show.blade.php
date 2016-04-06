@extends('layouts.settings')

@section('content')

    <h1>Software</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID.</th> <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $software->id }}</td> <td> {{ $software->name }} </td>
                </tr>
            </tbody>    
        </table>
    </div>

@endsection