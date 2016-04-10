<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Development Dashboard - Settings</title>
	<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
	<div class="container">
		<div class="row">
			<ul class="nav nav-tabs">
				<li><a href="/">Back to dashboard</a></li>
				<li role="presentation" class="active"><a href="{{ url('settings') }}">Settings</a></li>
			</ul>
		</div>
		<div class="row" style="margin-top:20px">
			<div class="col-lg-3">
				@include('partials.settingsHeader')
			</div>
			<div class="col-lg-9">
				@yield('content')
			</div>
		</div>
	</div>
	<script src="{{ asset('assets/js/jquery-1.12.1.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
</body>
</html>