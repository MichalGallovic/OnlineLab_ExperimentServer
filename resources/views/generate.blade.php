@extends("layouts.settings")

@section("content")
	<h1>Generate code for development</h1>
	<p>Once your files were generated, they will not be overwritten.</p>
	@include('partials.alert_codegenerator')
	<div class="table">
	    <table class="table table-bordered table-striped table-hover">
	        <thead>
	            <tr>
	                <th>S.No</th><th>Device Type</th><th>Software names</th><th>Action</th>
	            </tr>
	        </thead>
	        <tbody>
	        {{-- */$x=0;/* --}}
	        @foreach($devices as $item)
	            {{-- */$x++;/* --}}
	            <tr>
	                <td>{{ $x }}</td>
	                <td>{{ $item->type->name }}</a></td>
	                <td>@foreach($item->softwares->lists('name') as $softwareName){{ $softwareName }} @endforeach</a></td>
	                <td><a href="{{ url('generate/device/' . $item->id . '/code') }}" class="btn btn-xs btn-success">Generate code</a></td>
	            </tr>
	        @endforeach
	        </tbody>
	    </table>
	</div>
@endsection