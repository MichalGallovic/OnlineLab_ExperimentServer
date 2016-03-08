// Include Laravel CSRF token in every ajax request 
$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
    });
});

var vm = new Vue({
	el : "#app",
	experimentIntervalId: null,
	experimentMeasuringRate: null,
	ready : function() {
		this.getDevices();
		this.showExperiments();
	},
	data : {
		experiments : null,
		devices: null,
		activeDevice: null,
		activeMenu: "last_experiment",
		activeExperiment: null,
		experimentTypes: null,
		selectedExperiment: null,
		experimentData: null,
		experimentsHistory : [],
		selectedHistoryExperiment: null,
		outputArguments: null
	},
	methods : {
		runExperiment: function(event) {
			var inputFields = $(event.target).find('input');
			var inputValues = inputFields.map(function() {
				return $(this).val();
			}).get();

			var formData = this.makeRequestData(inputValues);

			$.ajax({
				type: "POST",
				url: "api/devices/" + this.activeDevice.id + "/run",
				data: formData
			});

			this.experimentIntervalId = setInterval(this.readExperimentData, 500);
					
		},
		readExperimentData: function() {
			var me = this;
			$.getJSON('api/devices/' + this.activeDevice.id + '/readexperiment')
			 .done(function(response) {
			 	var experimentData = me.formatChartInput(response.data);
			 	// console.log(experimentData);
			 	if(!me.experimentData && experimentData && experimentData.length > 0) {
			 		me.initGraph(experimentData);
			 		me.experimentData = experimentData;
			 		me.experimentMeasuringRate = response.measuring_rate;
			 		if(!me.experimentIntervalId) {
			 			me.experimentIntervalId = setInterval(me.readExperimentData, 500);
			 		}
			 	}

			 	if(me.experimentData) me.experimentData = experimentData;
			 })
			 .fail(function(response) {
			 	if(me.experimentData) {
			 		clearInterval(me.experimentIntervalId);
			 	}
			 });
		},
		stopExperiment: function() {
			clearInterval(this.experimentIntervalId);
			$.ajax({
				type: "GET",
				url: "api/devices/" + this.activeDevice.id + "/stop"
			});
		},
		makeRequestData: function(inputValues) {
			var experiment_input = {};

			for(var i = 0; i < inputValues.length; i++) {
				experiment_input[this.activeExperiment.input[i].name] = inputValues[i];
			}

			return {
				"experiment_type" : this.activeExperiment.name,
				"experiment_input": experiment_input
			};
		},
		formatChartInput: function(data) {
			var me = this;
			var series = [];
			$.each(data, function(index, measurement) {
				var measurementWithTime = [];
				$.each(measurement, function(index, value) {
					measurementWithTime.push([index*me.experimentMeasuringRate, value]);
				});
				series.push({
					type: "line",
					name: me.activeExperiment.output[index],
					data: measurementWithTime,
					visible: false
				});
			});

			return series;
		},
		formatChartInput2: function(data) {
			var me = this;
			var series = [];

			$.each(data, function(index, measurement) {
				var measurementWithTime = [];
				$.each(measurement, function(index, value) {
					measurementWithTime.push([index*me.experimentMeasuringRate, value]);
				});
				series.push({
					type: "line",
					name: me.outputArguments[index],
					data: measurementWithTime,
					visible: false
				});
			});

			return series;
		},
		showInfo: function() {
			this.activeMenu = "info";
		},
		showExperiments: function() {
			var me = this;
			this.activeMenu = "experiments";
			this.getDevicesPromise()
				.done(function(response) {
					$.each(response.data, function(index, device) {
						me.getExperimentsHistoryForDevice(device.id);
					});
				});
		},
		deactivateDevice: function() {
			this.activeDevice.active = false;
			this.activeDevice = null;
		},
		pickDevice: function(device) {
			this.activeMenu = "device";
			if(!this.activeDevice) {
				this.activeDevice = device;
				device.active = true;
			}
			if(!this.selectedExperiment) {
				this.selectedExperiment = 1;
			} else {
				// console.log(this.experimentData);
				this.initGraph(this.experimentData);
				// this.selectExperiment(this.selectedExperiment);
			}

		},
		selectExperiment: function(id) {
			var me = this;
			var experiments = this.activeDevice.experiments;
			
			if(experiments.length > 0) {
				$.each(experiments, function(index, experiment){
					if(experiment.id == id) {
						me.activeExperiment = experiment;
						me.readExperimentData();
					}
				});
			}

		},
		getLatestExperiment: function() {
			var me = this;

			$.getJSON('api/experiments/latest?include=measurements')
			 .done(function(response) {
			 	console.log(response.data.measurements.data.measurements);
		 		me.initGraph(response.data.measurements.data.measurements);
			 });
		},
		getExperimentsHistoryForDevice: function(id) {
			var me = this;

			$.getJSON('api/devices/' + id + "/experiments")
			 .done(function(response) {
			 	me.experimentsHistory = response.data;
			 });
		},
		showPreviousExperiment: function(experiment) {
			var me = this;
			this.selectedHistoryExperiment = experiment;
			this.getExperimentDataById(experiment.id)
				.done(function(response){
					me.outputArguments = response.data.output_arguments.data;
					me.experimentMeasuringRate = response.data.measurements.data.measurements_rate;
					var experimentData = me.formatChartInput2(response.data.measurements.data.measurements);
					me.initGraph(experimentData);
				});
		},
		getExperimentDataById: function(id) {
			return $.getJSON('api/experiments/' + id + "?include=measurements,output_arguments");
		},
		getDevices: function() {
			var me = this;

			$.getJSON('api/server/devices')
			 .done(function(response) {
			 	var devices = response.data;
			 	
			 	$.map(devices, function(device) {
			 		device.active = false;
			 		return device;
			 	});
			 	me.devices = devices;
			 	//@todo remove this - only for auto switching to
			 	//the first device
			 	// me.pickDevice(devices[0]);
			 });
		},
		//@Todo wrap every request to promises
		getDevicesPromise: function() {
			return $.getJSON('api/server/devices');
		},
		initGraph: function(series) {
			var me = this;

			var chart = $(".olm-graph").highcharts({
				
				title: {
				    text: me.experimentDescription
				},
				xAxis: {
					title: {
						text: "Simulation time"
					},
					labels: {
						formatter: function() {
							if(this.value <= 1000) {
								return this.value;
							}

							return this.value / 1000.00;
						}
					}
				},
				yAxis: {
				    title: {
				        text: 'Measurement value'
				    }
				},
				legend: {
					align: 'right',
		            verticalAlign: 'top',
		            layout: 'vertical',
		            x: 0,
		            y: 0,
		            itemMarginTop: 10
				},
				series: series
			});

			console.log($(".olm-graph").highcharts());
		}
	},
	computed : {
		experimentDescription: function() {
			return this.activeDevice.name + " running " + this.activeExperiment.name + " experiment"
		}
	}
});

vm.$watch('selectedExperiment', function(id) {
	this.selectExperiment(id);
});

vm.$watch('experimentData', function(newData, oldData) {
	var numMeasuremnetTypes = newData.length;
	var chart = $('.olm-graph').highcharts();

	// console.log(chart);
	if(chart) {
		// console.log(numMeasuremnetTypes);
		for(var i = 0; i < numMeasuremnetTypes; i++) {
			chart.series[i].setData(newData[i].data);
		}
	}
});