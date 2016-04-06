<div class="row">
	<ul class="nav nav-tabs">
		<li><a href="/">Back to dashboard</a></li>
		<li role="presentation" class="active"><a href="{{ url('settings') }}">Settings</a></li>
	</ul>
</div>
<div class="row" style="margin-top:20px;">
	<div class="col-lg-3">
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
			<li class="{{ active('reset') }}">
				<a href="{{ url('settings/reset') }}">Resetting</a>
			</li>
		</ul>
	</div>
</div>