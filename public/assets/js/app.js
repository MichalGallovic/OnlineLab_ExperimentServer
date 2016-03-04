new Vue({
	el : "#app",
	graph : null,
	ready : function() {
		// this.getExperiments();
		// this.getLatestExperiment();
		this.getDevices();
	},
	data : {
		experiments : [],
		devices: []
	},
	methods : {
		getExperimentTypes : function() {
			var me = this;

			$.getJSON('api/server/experiments')
			 .done(function(response) {
			 	console.log(response.data);
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
			 	me.devices = response.data;
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