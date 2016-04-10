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