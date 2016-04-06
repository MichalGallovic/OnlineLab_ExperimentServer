@extends('layouts.settings')

@section('content')

    <h1>Physical device <a href="{{ url('device/create') }}" class="btn btn-primary pull-right btn-sm">Add New Device</a></h1>
    <div class="table">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>S.No</th><th>Device Type</th><th>Port</th><th>Supported softwares</th><th>Default software</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            {{-- */$x=0;/* --}}
            @foreach($devices as $item)
                {{-- */$x++;/* --}}
                <tr>
                    <td>{{ $x }}</td>
                    <td><a href="{{ url('device', $item->id) }}">{{ $item->type->name }}</a></td>
                    <td><a href="{{ url('device', $item->id) }}">{{ $item->port }}</a></td>
                    <td><a href="{{ url('device', $item->id) }}">@foreach($item->softwares->lists('name') as $softwareName){{ $softwareName }} @endforeach</a></td>
                    <td><a href="{{ url('device', $item->id) }}">{{ $item->defaultSoftware }}</a></td>
                    <td>
                        <a href="{{ url('device/' . $item->id . '/edit') }}">
                        <button type="submit" class="btn btn-primary btn-xs">Update</button>
                        </a> /
                        {!! Form::open([
                            'method'=>'DELETE',
                            'url' => ['device', $item->id],
                            'style' => 'display:inline'
                        ]) !!}
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-xs']) !!}
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="pagination"> {!! $devices->render() !!} </div>
    </div>

@endsection
