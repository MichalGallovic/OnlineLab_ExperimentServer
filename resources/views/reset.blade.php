@extends("layouts.settings")

@section("content")
	<h1>Reset app server database</h1>
	@if(session('flash_message'))
    <div class="alert alert-info">
        <p>{{ session('flash_message') }}</p>
    </div>
    @endif
	<a href="{{ url('reset/database') }}" class="btn btn-danger">Reset</a>
@endsection