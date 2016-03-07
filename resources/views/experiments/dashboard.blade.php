<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Development Dashboard</title>
	<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body id="app">
	{{ csrf_field() }}
	<div class="container">
		<div class="row">
			<ul class="nav nav-tabs">
				<li v-on:click="showInfo" v-bind:class="{ 'active' : (activeMenu == 'info')}"><a href="#Info">Info</a></li>
				<li v-on:click="showExperiments" v-bind:class="{ 'active' : (activeMenu == 'experiments')}"><a href="#Experiments">Experiments history</a></li>
				<li class="pull-right" v-bind:class="{ 'active' : (device.active &&  activeMenu == 'device') }" v-for="device in devices" v-on:click="pickDevice(device)"><a href="#@{{ device.name }}">
					@{{ device.name }}
				</a></li>
				<li class="pull-right disabled"><a>Devices -> </a></li>
			</ul>
		</div>

		<div class="row" v-if="activeMenu == 'device'">
			<div class="col-lg-9" v-if="activeDevice">
				<div class="olm-graph">
					blabla car
				</div>
			</div>
			<div class="col-lg-3">
				<div class="row">
					<h4>Select experiment type:</h4>
					<select class="form-control" v-model="selectedExperiment">
					  <option v-bind:value="experiment.id" v-for="experiment in activeDevice.experiments">@{{ experiment.name }}</option>
					</select>
				</div>
				<div class="row" style="margin-top:20px">
					<form v-on:submit.prevent="runExperiment">
						<div class="form-group" v-for="argument in activeExperiment.input">
							<label class="col-xs-9">@{{ argument.title }}</label>
							<input class="col-xs-3" type="text" v-bind:name="argument.name" v-bind:value="argument.placeholder">
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<button type="submit" class="btn-success">Run</button>
								<button type="button" class="btn-danger" v-on:click="stopExperiment">Stop</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="row" v-if="activeMenu == 'info'">
			<h2>Nejake info</h2>
		</div>
		<div class="row" v-if="activeMenu == 'experiments'">
			<h2>Last Experiment</h2>
		</div>
	</div>
	<script src="{{ asset('assets/js/jquery-1.12.1.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/js/vue.js') }}"></script>
	<script src="{{ asset('assets/js/highcharts.js') }}"></script>
	<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>