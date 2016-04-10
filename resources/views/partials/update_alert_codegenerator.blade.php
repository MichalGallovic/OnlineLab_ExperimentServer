@if(session('flash_message_update'))
<div class="alert alert-info">
	<strong>{{ session('flash_message_update') }}</strong>
	<p>If you added new software implementation, head to  <a href="{{ url('generate') }}">Code generation</a> to generate the code.</p>
</div>
@endif