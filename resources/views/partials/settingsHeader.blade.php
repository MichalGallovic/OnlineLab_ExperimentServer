<ul class="nav nav-pills nav-stacked">
	<li class="{{ active('devicetype') }}">
		<a href="{{ url('devicetype') }}">Devices types</a>
	</li>
	<li class="{{ active('software') }}">
		<a href="{{ url('software') }}">Softwares</a>
	</li>
	<li class="{{ active('device') }}">
		<a href="{{ url('device') }}">Physical devices</a>
	</li>
	<li class="{{ active('experiment') }}">
		<a href="{{ url('experiment') }}">Experiments</a>
	</li>
	<li class="{{ active('generate') }}">
		<a href="{{ url('generate') }}">Code generation</a>
	</li>
	<li class="{{ active('reset') }}">
		<a href="{{ url('reset') }}">Resetting</a>
	</li>
</ul>