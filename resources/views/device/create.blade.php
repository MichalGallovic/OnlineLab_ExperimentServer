@extends('layouts.settings')

@section('content')

    <h1>Add New Physical device</h1>
    <hr/>

    {!! Form::open(['url' => 'device', 'class' => 'form-horizontal']) !!}

    <div class="form-group {{ $errors->has('device_type') ? 'has-error' : ''}}">
        {!! Form::label('device_type', 'Device type: ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::select('device_type', $devicetypes, null, ['class' => 'form-control']) !!}
            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
        </div>
    </div>
    
    <div class="form-group {{ $errors->has('port') ? 'has-error' : ''}}">
        {!! Form::label('port', 'Port: ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('port', null, ['class' => 'form-control']) !!}
            {!! $errors->first('port', '<p class="help-block">:message</p>') !!}
        </div>
    </div>

    <div class="form-group {{ $errors->has('softwares') ? 'has-error' : ''}}">
        {!! Form::label('', 'Pick softwares: ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            @foreach($softwares as $software)
            <span>{{ $software->name }}</span>
            {!! Form::checkbox('softwares[]', $software->id) !!}
            <br>
            @endforeach
            {!! $errors->first('softwares', '<p class="help-block">:message</p>') !!}
        </div>
    </div>

    <div class="form-group {{ $errors->has('default_software') ? 'has-error' : ''}}">
        {!! Form::label('default_software', 'Default software: ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            @foreach($softwares as $software)
            <span>{{ $software->name }}</span>
            {!! Form::radio('default_software', $software->id) !!}
            <br>
            @endforeach
            {!! $errors->first('default_software', '<p class="help-block">:message</p>') !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-3">
            {!! Form::submit('Create', ['class' => 'btn btn-primary form-control']) !!}
        </div>
    </div>
    {!! Form::close() !!}


@endsection