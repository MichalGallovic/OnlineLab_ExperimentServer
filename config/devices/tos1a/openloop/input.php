<?php

return [
	"start"  =>  [
		[
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Napätie ventilátor",
			"placeholder"	=>	20,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_lamp",
			"rules"	=>	"required",
			"title"	=>	"Napätie lampy",
			"placeholder"	=>	60,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_led",
			"rules"	=>	"required",
			"title"	=>	"Napätie ledky",
			"placeholder"	=>	0,
			"type"	=>	"text"
		],
		[
			"name"	=>	"t_sim",
			"rules"	=>	"required",
			"title"	=>	"Čas simulácie",
			"placeholder"	=>	10,
			"type"	=>	"text",
			"meaning"	=>	"experiment_duration"
		],
		[
			"name"	=>	"s_rate",
			"rules"	=>	"required",
			"title"	=>	"Vzorkovací čas",
			"placeholder"	=>	200,
			"type"	=>	"text",
			"meaning"	=>	"sampling_rate"
		],
		[
			"name"	=>	"test_field",
			"rules"	=>	"",
			"title"	=>	"Switcher",
			"type"	=>	"radio",
			"values"=>	["Walking","Flying"]
		],
		[
			"name"	=>	"walking",
			"rules"	=>	"",
			"title"	=>	"Walking",
			"type"	=>	"text",
			"visible"	=>	["test_field" => "Walking"]
		],
		[
			"name"	=>	"flying",
			"rules"	=>	"",
			"title"	=>	"Flying",
			"type"	=>	"text",
			"visible"	=>	["test_field" => "Walking"]
		]
	],
	"stop" => [],
	"change" => [
		[
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Napätie ventilátor",
			"placeholder"	=>	20,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_lamp",
			"rules"	=>	"required",
			"title"	=>	"Napätie lampy",
			"placeholder"	=>	60,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_led",
			"rules"	=>	"required",
			"title"	=>	"Napätie ledky",
			"placeholder"	=>	0,
			"type"	=>	"text"
		]
	]
];