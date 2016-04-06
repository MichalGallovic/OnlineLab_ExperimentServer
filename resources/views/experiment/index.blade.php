@extends('layouts.settings')

@section('content')

    <h1>Experiments</h1>
    <div class="table">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>S.No</th><th>Device Type</th><th>Software name</th>
                </tr>
            </thead>
            <tbody>
            {{-- */$x=0;/* --}}
            @foreach($experiments as $item)
                {{-- */$x++;/* --}}
                <tr>
                    <td>{{ $x }}</td>
                    <td>{{ $item->device->type->name }}</a></td>
                    <td>{{ $item->software->name }}</a></td>
                    
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="pagination"> {!! $experiments->render() !!} </div>
    </div>

@endsection
