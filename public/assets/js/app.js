new Vue({
	el : "#app",
	graph : null,
	ready : function() {
		// this.getExperiments();
		// this.getLatestExperiment();
		this.getDevices();
	},
	data : {
		experiments : null,
		devices: null,
		activeDevice: null,
		activeExperimentType: null,
		experimentTypes: null
	},
	methods : {
		pickDevice: function(device) {
			this.activeDevice = device;
			device.active = true;
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
			 		console.log(device);
			 		return device;
			 	});
			 	me.devices = devices;
			 });
		},
		initGraph: function(data) {
			var series = [];
			$.each(data, function(index, measurement) {
				series.push({
					type: "line",
					name: "Series " + index,
					data: measurement
				});
			});

			this.graph = $(".olm-graph").highcharts({
				
				title: {
				    text: 'Fruit Consumption'
				},
				yAxis: {
				    title: {
				        text: 'Fruit eaten'
				    }
				},
				series: series
			});
		}
	}
});