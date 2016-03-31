// Include Laravel CSRF token in every ajax request 
$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
    });
});

Vue.component('olm-graph', {
	template: "#graph-template",
	props: {
		series: {
			default: function() {
				return [{data:[]}];
			}
		},
		description: {
			type: String,
			default: "Empty graph"
		}
	},
	ready: function() {
		this.initGraph(this.series);
	},
	methods: {
		initGraph: function(series) {
			var me = this;
			
			this.getjQueryGraph().highcharts({
				title: {
				    text: me.description
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
		},
		getjQueryGraph: function() {
			return $(this.$els.graph);
		}
	},
	watch: {
		series: function(newSeries, oldSeries) {
			var chart = this.getjQueryGraph().highcharts();

			if(newSeries.length == oldSeries.length) {
				for(var i = 0; i < newSeries.length; i++) {
					chart.series[i].setData(newSeries[i].data)
				}
			} else {
				chart.destroy();
				this.initGraph(newSeries);
			}

		},
		description: function(newDescription, oldDescription) {
			var chart = this.getjQueryGraph().highcharts();

			chart.setTitle({
				text: newDescription
			})
		}
	},
	events : {
		toggleLayout: function() {
			var me = this;
			setTimeout(function() {
				me.getjQueryGraph().highcharts().reflow();
			}, 100);
		}
	}
});

var vm = new Vue({
	el : "#app",
	experimentIntervalId: null,
	experimentMeasuringRate: null,
	ready : function() {
		$(this.$el).show();
		this.getDevices();
		this.showExperiments();		
	},
	data : {
		fullWidth: false,
		waitingForData: false,
		devices: null,
		activeDevice: null,
		activeMenu: "info",
		activeSoftware: null,
		selectedExperiment: null,
		experimentData: [{data:[]}],
		experimentsHistory : [],

		pastExperiment: {
			series: [{data:[]}],
			id: null,
			description: null
		}
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
				url: "api/devices/" + this.activeDevice.id + "/start",
				data: formData
			});

			this.waitingForData = true;

			this.startListening();

			// setTimeout(this.startListening(), 1000);
					
		},
		startListening: function() {
			this.experimentIntervalId = setInterval(this.readExperimentData, 500);
		},
		readExperimentData: function() {
			var me = this;
			$.getJSON('api/devices/' + this.activeDevice.id + '/readexperiment')
			 .done(function(response) {
			 	me.waitingForData = false;
			 	me.experimentData = me.formatGraphInput(
			 		response.data, 
			 		response.measuring_rate,
			 		me.activeSoftware.output);

			 })
			 .fail(function(response) {
			 	if(!me.waitingForData) {
			 		clearInterval(me.experimentIntervalId);
			 	}
			 });
		},
		getDeviceStatus: function(id) {
			return $.getJSON('api/devices/' + id);
		},
		isRunningExperiment: function() {
			var me = this;
			$.each(this.devices, function(index, device) {
				me.getDeviceStatus(device.id)
				  .done(function(response) {
				  	if(response.status == "experimenting") {
				  		me.pickDevice(device);
				  		me.startListening();
				  		noty ({
				  			text : "Device " + device.name + " is running experiment!",
				  			theme: "relax",
				  			layout: "topRight",
				  			timeout : 5000,
				  			type: 'information'
				  		});
				  	}
				});
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
				experiment_input[this.activeSoftware.input.start[i].name] = inputValues[i];
			}

			return {
				"software" : this.activeSoftware.name,
				"input": experiment_input
			};
		},
		formatGraphInput: function(data, rate, output_arguments) {
			console.log(output_arguments);
			var me = this;
			var series = [];
			$.each(data, function(index, measurement) {
				var measurementWithTime = [];
				$.each(measurement, function(index, value) {
					measurementWithTime.push([index*rate, value]);
				});
				series.push({
					type: "line",
					name: output_arguments[index].title,
					data: measurementWithTime,
					visible: false
				});
			});

			return series;
		},
		showInfo: function() {
			introJs().start();
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
		pickDevice: function(device) {
			this.activeMenu = "device";
			this.activeDevice = device;
			device.active = true;
			
			if(!this.selectedExperiment) {
				this.selectedExperiment = 1;
			}

		},
		selectExperiment: function(id) {
			var me = this;
			var softwares = this.activeDevice.softwares;
			
			if(softwares.length > 0) {
				$.each(softwares, function(index, experiment){
					if(experiment.id == id) {
						me.activeSoftware = experiment;
					}
				});
			}

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

			this.pastExperiment.id = experiment.id;
			this.pastExperiment.description = "Device: " + experiment.device + " SW Environment: " + experiment.software;
			this.getExperimentDataById(experiment.id)
				.done(function(response){
					me.pastExperiment.series = me.formatGraphInput(
						response.data.measurements.data.measurements,
						response.data.measurements.data.measurements_rate,
						response.data.output_arguments.data
					);
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
		toggleLayout: function() {
			this.fullWidth  = !this.fullWidth;
			this.$broadcast('toggleLayout');
		}
	},
	computed : {
		experimentDescription: function() {
			return this.activeDevice.name + " running " + this.activeSoftware.name + " experiment"
		}
	}
});

vm.$watch('selectedExperiment', function(id) {
	this.selectExperiment(id);
});

vm.$watch('activeSoftware', function() {
	
});

vm.$watch('devices', function() {
	this.isRunningExperiment();
});