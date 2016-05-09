@extends('layouts.settings')

@section('content')

    <h1>Edit Physical device</h1>
    <hr/>

    {!! Form::model($device, [
        'method' => 'PATCH',
        'url' => ['device', $device->id],
        'class' => 'form-horizontal'
    ]) !!}

    <div class="form-group {{ $errors->has('device_type') ? 'has-error' : ''}}">
        {!! Form::label('device_type', 'Device type: ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            <input type="hidden" name="device_type" value="{{ $device->type->name }}">
            <input type="text" name="device_type" class="form-control" disabled value="{{ $device->type->name }}">
            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
        </div>
    </div>

    <div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
        {!! Form::label('name', 'Device name: ', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
            {!! Form::text('name', null, ['class' => 'form-control','placeholder' => 'Unique device name']) !!}
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
            @if($device->softwares->contains($software->id))
                {!! Form::checkbox('softwares[]', $software->id, true) !!}
            @else
                {!! Form::checkbox('softwares[]', $software->id) !!}
            @endif
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
            @if($device->defaultExperiment->software->id == $software->id)
                <input name="default_software" type="radio" value="{{ $software->id }}" id="default_software" checked>
            @else
                {!! Form::radio('default_software', $software->id) !!}
            @endif
            <br>
            @endforeach
            {!! $errors->first('default_software', '<p class="help-block">:message</p>') !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-3">
            {!! Form::submit('Update', ['class' => 'btn btn-primary form-control']) !!}
        </div>
    </div>
    {!! Form::close() !!}


@endsection