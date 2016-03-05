<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Development Dashboard</title>
	<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body id="app">
	<div class="container">
		<div class="row">
			<ul class="nav nav-tabs">
				<li role="presentation" v-bind:class="{ 'active' : device.active }" v-for="device in devices" v-on:click="pickDevice(device)"><a href="#">
					@{{ device.name }}
				</a></li>
			</ul>
		</div>
		<div class="row" v-if="activeDevice">
			<div class="col-lg-9 olm-graph" v-if="activeDevice">
				graf
			</div>
			<div class="col-lg-3">
				<h4>Select experiment type:</h4>
				<select class="form-control" v-model="activeExperimentType">
				  <option v-for="experiment_type in activeDevice.experiment_types">@{{ experiment_type.name }}</option>
				</select>
			</div>
		</div>
		<div class="row" v-else>
			<h2>^^ Pick desired device, you want to develop upon</h2>
		</div>
	</div>
	<script src="{{ asset('assets/js/jquery-1.12.1.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/js/vue.js') }}"></script>
	<script src="{{ asset('assets/js/highcharts.js') }}"></script>
	<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>