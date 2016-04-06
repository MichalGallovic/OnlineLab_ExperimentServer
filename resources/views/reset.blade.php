@extends("layouts.settings")

@section("content")
	<h1>Reset app server database</h1>
	<a href="{{ url('reset/database') }}" class="btn btn-danger">Reset</a>
@endsection