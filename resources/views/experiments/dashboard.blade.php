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
				<li role="presentation" class="active" v-for="device in devices"><a href="#">
					@{{ device.name }}
				</a></li>
				<li role="presentation" class="active" v-for="device in devices"><a href="#">
					@{{ device.name }}
				</a></li>
			</ul>
			<div class="col-lg-6 olm-graph">
				
			</div>
			<div class="col-lg-6">
				
			</div>
		</div>
	</div>
	<script src="{{ asset('assets/js/jquery-1.12.1.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/js/vue.js') }}"></script>
	<script src="{{ asset('assets/js/highcharts.js') }}"></script>
	<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>