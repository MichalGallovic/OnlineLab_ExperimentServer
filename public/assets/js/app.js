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
		// this.getExperiments();
		// this.getLatestExperiment();
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
		experimentData: null
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
		showInfo: function() {
			// this.deactivateDevice();
			this.activeMenu = "info";
		},
		showExperiments: function() {
			// this.deactivateDevice();
			this.activeMenu = "experiments";
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
				console.log(this.experimentData);
				this.initGraph(this.experimentData);
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
		getExperimentTypes : function() {
			var me = this;

			$.getJSON('api/server/experiments')
			 .done(function(response) {

			 	me.experiments = response.data;
			 });
		},
		getLatestExperiment: function() {
			var me = this;

			$.getJSON('api/experiments/latest?include=measurements')
			 .done(function(response) {
			 	console.log(response.data.measurements.data.measurements);
		 		me.initGraph(response.data.measurements.data.measurements);
			 });
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

			console.log(chart);
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