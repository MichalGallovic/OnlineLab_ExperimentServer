<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Development Dashboard</title>
	<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
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
					data-step="@{{ (index + 2) }}"
					data-intro="Run and see the results of experiments on device @{{ device.name }}" 
					class="pull-right" 
					v-bind:class="{ 'active' : (device.active &&  activeMenu == 'device') }" 
					v-for="(index, device) in devices" 
					v-on:click="pickDevice(device)">
						<a href="#@{{ device.name }}">
							@{{ device.name }}
						</a>
					</li>
					<li class="pull-right disabled"><a>Devices -> </a></li>
				</ul>
			</div>

			<div class="row" v-if="activeMenu == 'device'">
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

				<div 
				v-bind:class="{ 'col-lg-12' : fullWidth, 'col-lg-3' : !fullWidth }" 
				>
					<div class="row">
						<h4>Select software environment:</h4>
						<select class="form-control" v-model="selectedExperiment">
						  <option v-bind:value="software.id" v-for="software in activeDevice.softwares">@{{ software.name }}</option>
						</select>
					</div>
					<div class="row" style="margin-top:20px">
						<form v-on:submit.prevent="runExperiment">
							<div class="form-group" v-for="argument in activeSoftware.input">
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