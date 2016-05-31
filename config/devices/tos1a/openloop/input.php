<?php

return [
	"start"  =>  [
		[
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Fan voltage",
			"placeholder"	=>	20,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_lamp",
			"rules"	=>	"required",
			"title"	=>	"Lamp voltage",
			"placeholder"	=>	60,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_led",
			"rules"	=>	"required",
			"title"	=>	"LED voltage",
			"placeholder"	=>	0,
			"type"	=>	"text"
		],
		[
			"name"	=>	"t_sim",
			"rules"	=>	"required",
			"title"	=>	"Simulation time",
			"placeholder"	=>	10,
			"type"	=>	"text",
			"meaning"	=>	"experiment_duration"
		],
		[
			"name"	=>	"s_rate",
			"rules"	=>	"required",
			"title"	=>	"Sampling rate",
			"placeholder"	=>	200,
			"type"	=>	"text",
			"meaning"	=>	"sampling_rate"
		]
	],
	"stop" => [],
	"change" => [
		[
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Fan Voltage",
			"placeholder"	=>	20,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_lamp",
			"rules"	=>	"required",
			"title"	=>	"Lamp voltage",
			"placeholder"	=>	60,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_led",
			"rules"	=>	"required",
			"title"	=>	"LED voltage",
			"placeholder"	=>	0,
			"type"	=>	"text"
		]
	]
];