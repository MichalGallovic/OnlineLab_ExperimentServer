@extends("layouts.settings")

@section("content")
	<h1>Generate code for experiment development</h1>
	@if(session('messages'))
		@if(isset(session('messages')["error"]))
		<div class="alert alert-warning">
			<p><strong>Warnings & errors</strong></p>
			@foreach(session('messages')["error"] as $error)
				<p>{{ $error }}</p>
			@endforeach
		</div>
		@endif
		@if(isset(session('messages')["new"]))
		<div class="alert alert-success">
			<p><strong>Generated folders & files</strong></p>
			@foreach(session('messages')["new"] as $message)
				<p>{{ $message }}</p>
			@endforeach
		</div>
		@endif
	@endif
	<div class="table">
	    <table class="table table-bordered table-striped table-hover">
	        <thead>
	            <tr>
	                <th>S.No</th><th>Device Type</th><th>Software name</th><th>Action</th>
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
	                <td><a href="{{ url('generate/experiment/' . $item->id . '/code') }}" class="btn btn-xs btn-success">Generate code</a></td>
	            </tr>
	        @endforeach
	        </tbody>
	    </table>
	</div>
@endsection