@extends('layouts.settings')

@section('content')

    <h1>Physical device</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th><th>Type</th><th>Port</th><th>Supported Softwares</th><th>Default software</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $device->name }}</td>
                    <td>{{ $device->type->name }}</td>
                    <td>{{ $device->port }}</td>
                    <td>@foreach($device->softwares->lists('name') as $softwareName){{ $softwareName }} @endforeach</td>
                    <td>{{ $device->defaultSoftware }}</td>
                </tr>
            </tbody>    
        </table>
    </div>

@endsection