<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Development Dashboard</title>
	<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/introjs.min.css') }}">
</head>
<body>
	<div id="app" style="display: none;">
		{{ csrf_field() }}
		<div class="container">
			<div class="row">
				<ul class="nav nav-tabs">
					<li v-on:click="showInfo" v-bind:class="{ 'active' : (activeMenu == 'info')}"><a href="#Info">Info</a></li>
					<li v-on:click="toggleLayout">
						<a href="#@{{ layout }}">Toggle layout</a>
					</li>
					<li
					data-step="1"
					data-intro="Here you can replay all your previous experiments" 
					v-on:click="showExperiments" 
					v-bind:class="{ 'active' : (activeMenu == 'experiments')}">
						<a href="#Experiments">Experiments history</a>
					</li>
					<li
					data-step="2"
					data-intro="Here you can add new experiments or reset environment"
					>
					<a href="{{ url("settings") }}">Crud & Settings</a>
					</li>
					<li
					data-step="@{{ (index + 3) }}"
					data-intro="Run and see the results of experiments on device @{{ device.name }}" 
					class="pull-right" 
					v-bind:class="{ 'active' : (device.name == activeDevice.name &&  activeMenu == 'device') }" 
					v-for="(index, device) in devices" 
					v-on:click="pickDevice(device)">
						<a href="#@{{ device.name }}">
							@{{ device.name }}
						</a>
					</li>
					<li class="pull-right" v-if="!devices || devices.length == 0"><a href="/devicetype">Add new device</a></li>
					<li class="pull-right disabled"><a>Devices -> </a></li>
				</ul>
			</div>

			<div class="row" v-if="activeMenu == 'device'">
				<div v-if="selectedCommand">
					<div v-if="outputType == 'graph'">
						<div 
						v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-9' : !fullWidth }"
						v-if="activeDevice && !waitingForData">
							<olm-graph 
								:description="experimentDescription"
								:series="experimentData"
							></olm-graph>
						</div>
						<div 
						v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-9' : !fullWidth }" 
						v-else>
							<div class="spinner"></div>
							<span 
							style="display: inline-block;
							 	width: 100%;
							  	text-align:center; 
							  	font-size:17px;">
							  	Initializing @{{ activeSoftware.name }} experiment ...
							</span>
						</div>
					</div>
					<div v-else
						v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-9' : !fullWidth }"
					>
						<olm-debug
						:output="commandOutput"
						:description="commandDescription"
						>
						</olm-debug>
					</div>
				</div>
				<div v-else>
					<div 
					v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-9' : !fullWidth }"
					style="margin-top:60px;"
					v-if="activeDevice && !waitingForData">
						<div style="text-align: center">
							<i class="fa fa-4x fa-flask" style="font-size:6em"></i>
						</div>
						<div style="padding:50px; font-size: 18px; text-align: center">
							<span>
								Once you implement a command, you can see its output here. Graph for <strong>start</strong> command and API response for <strong>other</strong> commands.
							</span>
						</div>
					</div>
				</div>

				<div 
				v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-3' : !fullWidth }" 
				>
					<div class="row">
						<h4>Select software environment:</h4>
						<select class="form-control" v-model="activeSoftware">
						  <option v-bind:value="software" v-for="(index, software) in activeDevice.softwares" v-bind:selected="">@{{ software.name }}</option>
						</select>
					</div>
					<div class="row">
						<h4>Select command:</h4>
						<select class="form-control" v-model="selectedCommand" v-if="activeSoftware.commands.length > 0">
							<option v-bind:value="command" v-for="command in activeSoftware.commands">@{{ command }}</option>
						</select>
						<span v-else>No commands implemented yet!</span>
					</div>
					<div class="row" style="margin-top:20px" v-show="selectedCommand">
						<form v-on:submit.prevent="runCommand">
							<div class="form-group" v-for="argument in activeSoftware.input[selectedCommand]">
								<label class="col-xs-9">@{{ argument.title }}</label>
								<input class="col-xs-3" type="text" v-bind:name="argument.name" v-bind:value="argument.placeholder">
							</div>
							<div class="form-group">
								<button type="submit" class="btn-success" style="margin-top:20px;">Run Command</button>
							</div>
						</form>
					</div>
				</div>
			</div>

		
			<div class="row" v-if="activeMenu == 'experiments'">
				<div 
				v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-9' : !fullWidth }" 
				>
					<olm-graph 
						:description="pastExperiment.description"
						:series="pastExperiment.series"
					></olm-graph>
				</div>
				<div 
				v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-3' : !fullWidth }" 
				>
					<h4>Previous experiments</h4>
					<ul 
					class="list-group" 
					style="max-height: 500px; overflow: auto;"
					v-if="experimentsHistory.length > 0">
					  <li 
					  v-bind:class="{'active' : pastExperiment.id == experiment.id}" 
					  class="list-group-item" 
					  v-for="experiment in experimentsHistory" 
					  v-on:click="showPreviousExperiment(experiment)"
					  style="cursor: pointer;"
					  >
					  @{{ experiment.device }} - @{{ experiment.software }} | @{{ experiment.started_at }}
					  </li>
					</ul>
					<div class="spinner" v-else></div>
				</div>
			</div>
		</div>

		<template id="graph-template">
			<div v-el:graph class="olm-graph" v-show="series.length > 1">
				
			</div>
			<div class="olm-graph-placeholder" v-show="series.length <= 1">
			</div>
		</template>
		<template id="debug-template">
			<h4 style="margin:20px">@{{ description }}</h4>
			<pre style="margin:20px">@{{ output | json }}</pre>
		</template>
	</div>
	<script src="{{ asset('assets/js/jquery-1.12.1.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/js/vue.js') }}"></script>
	<script src="{{ asset('assets/js/highcharts.js') }}"></script>
	<script src="{{ asset('assets/js/intro.min.js') }}"></script>
	<script src="{{ asset('assets/js/noty/jquery.noty.packaged.min.js') }}"></script>
	<script src="{{ asset('assets/js/noty/relax.js') }}"></script>
	<script src="{{ asset('assets/js/noty/topRight.js') }}"></script>
	<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>