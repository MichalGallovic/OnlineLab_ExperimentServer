// Include Laravel CSRF token in every ajax request 
$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
    });
});
Vue.component('olm-webcam', {
	template: "#webcam-template",
	ready: function() { init(); }
});

Vue.component('olm-input',{
	template: "#input-template",
	props: {
		label:null,
		type : {
			default : function() {
				return "text";
			}
		},
		placeholder: {
			default : function() {
				return "This is placeholder";
			}
		},
		values: [],
		name : null,
	},
	data: function() {
		return {
			input : null
		}
	},
	ready: function() {
		if(this.type == "checkbox") {
			this.input = [];
		}
		if(this.type == "select") {
			this.input = this.values[0];
		}

		if(this.type == "radio") {
			this.input = this.values[0];
		}
	},
	methods : {
		getInputValues: function() {
			// var inputFields = $(this.$els.input).find(':input');
			// var inputValues = inputFields.map(function() {
			// 	return $(this).val();
			// }).get();

			// return inputValues;
			return this.input;
		}
	}
});

Vue.component('olm-debug', {
	template: "#debug-template",
	props: {
		output: null,
		description: "Command description"
	}
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
		            itemMarginTop: 8
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
		selectedCommand: null,
		commandOutput: {
			json : null,
			text : null,
			error : false
		},
		experimentData: [{data:[]}],
		experimentsHistory : [],

		pastExperiment: {
			series: [{data:[]}],
			id: null,
			description: null
		}
	},
	methods : {
		runCommand: function(event) {
			var me = this;

			var inputValues = [];

			$.each(this.$children, function(index, component) {
				if($.isFunction(component.getInputValues)) {
					inputValues.push(component.getInputValues());
				}
			});

			var formData = this.makeRequestData(inputValues);

			this.clearCommandOutput();

			if(this.selectedCommand == "start") {
				this.waitingForData = true;
				this.startListening();
			}

			$.ajax({
				type: "POST",
				url: "api/devices/" + this.activeDevice.id ,
				data: formData
			}).done(function(response) {
				me.commandOutput = {};
				me.commandOutput.json = response;
				me.commandOutput.text = response;
			}).fail(function(response) {
				me.stopListening();
				me.waitingForData = false;
				me.commandOutput = {};
				me.commandOutput.json = response.responseJSON;
				me.commandOutput.error = true;
				me.commandOutput.text = response.responseText.replace(/<style>(.|\n)*<\/style>/g,'');
			});

		},
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
		stopListening: function() {
			clearInterval(this.experimentIntervalId);
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
			 		me.stopListening();
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
		makeRequestData: function(inputValues) {
			var command_input = {};

			for(var i = 0; i < inputValues.length; i++) {
				command_input[this.activeSoftware.input[this.selectedCommand][i].name] = inputValues[i];
			}

			return {
				"software" : this.activeSoftware.name,
				"command" : this.selectedCommand,
				"input": command_input
			};
		},
		formatGraphInput: function(data, rate, output_arguments) {

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
					if(response.data.length == 0 ){
						me.hideExperimentsSpinner();
					}
					$.each(response.data, function(index, device) {
						me.getExperimentsHistoryForDevice(device.id);
					});
				});
		},
		hideExperimentsSpinner: function() {
			var spinner = $('.spinner');
			spinner.text("You didn't run any experiments yet!");
			spinner.removeClass('spinner');
		},
		pickDevice: function(device) {
			this.activeMenu = "device";
			this.activeDevice = device;
			this.activeDevice.active = true;
			this.activeSoftware = device.softwares[0];
			this.selectedCommand = this.activeSoftware.commands[0];
			this.experimentRea
			this.clearCommandOutput();
			this.clearExperimentData();
		},
		deleteExperimentLogs: function() {
			var me = this;
			$.getJSON('api/experiments/delete');
			$.each(this.devices,function(index, device) {
				me.getExperimentsHistoryForDevice(device.id);
			});
		},
		clearCommandOutput: function() {
			this.commandOutput = {
				json : null,
				text : null,
				error: false
			}
		},
		clearExperimentData: function() {
			this.experimentData = [{data:[]}];
		},
		getExperimentsHistoryForDevice: function(id) {
			var me = this;

			$.getJSON('api/devices/' + id + "/experiments")
			 .done(function(response) {
			 	if(response.data.length == 0) {
			 		me.hideExperimentsSpinner();
			 	}
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
			 })
			 .fail(function(response) {
			 	noty ({
			 		text : response.responseJSON.error.message,
			 		theme: "relax",
			 		layout: "topRight",
			 		timeout : 5000,
			 		type: 'error'
			 	});
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
		},
		commandDescription: function() {
			return this.selectedCommand + " command" + " on " + this.activeDevice.name + " software " + this.activeSoftware.name;
		}
	}
});

vm.$watch('selectedCommand', function() {
	this.clearCommandOutput();
});

vm.$watch('activeSoftware', function(newActiveSoftware) {
	this.selectedCommand = newActiveSoftware.commands[0];
});

// vm.$watch('devices', function() {
// 	this.isRunningExperiment();
// });