@extends('layouts.settings')

@section('content')

    <h1>Devicetype <a href="{{ url('devicetype/create') }}" class="btn btn-primary pull-right btn-sm">Add New Devicetype</a></h1>
    <div class="table">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>S.No</th><th>Name</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            {{-- */$x=0;/* --}}
            @foreach($devicetype as $item)
                {{-- */$x++;/* --}}
                <tr>
                    <td>{{ $x }}</td>
                    <td><a href="{{ url('devicetype', $item->id) }}">{{ $item->name }}</a></td>
                    <td>
                        <a href="{{ url('devicetype/' . $item->id . '/edit') }}">
                            <button type="submit" class="btn btn-primary btn-xs">Update</button>
                        </a> /
                        {!! Form::open([
                            'method'=>'DELETE',
                            'url' => ['devicetype', $item->id],
                            'style' => 'display:inline'
                        ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-xs']) !!}
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="pagination"> {!! $devicetype->render() !!} </div>
    </div>

@endsection
