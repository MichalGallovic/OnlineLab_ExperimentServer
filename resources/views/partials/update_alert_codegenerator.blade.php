@if(session('flash_message'))
<div class="alert alert-info">
	<strong>{{ session('flash_message') }}</strong>
	<p>If you added new software implementation, head to  <a href="{{ url('generate') }}">Code generation</a> to generate the code.</p>
</div>
@endif