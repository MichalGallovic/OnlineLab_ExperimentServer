<?php

return [
	"start"  =>  [
		[
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Napätie ventilátora",
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
			"type"	=>	"text"
		],
		[
			"name"	=>	"s_rate",
			"rules"	=>	"required",
			"title"	=>	"Vzorkovací čas",
			"placeholder"	=>	200,
			"type"	=>	"text"
		]
	],
	"init"	=>	[
		[
			"name"	=>	"raz",
			"rules"	=>	"",
			"title"	=>	"Initial temperature",
			"placeholder"	=>	"25",
			"type"	=>	"radio",
			"values"	=>	["PID","Vlastny","Normalka"]
		],
		[
			"name"	=>	"dva",
			"rules"	=>	"",
			"title"	=>	"regulatoris",
			"placeholder"	=>	"",
			"type"	=>	"checkbox",
			"values"	=>	["prvy","druhy","treti"]
		],
		[
			"name"	=>	"tri",
			"rules"	=>	"",
			"title"	=>	"regulatoris",
			"placeholder"	=>	"",
			"type"	=>	"select",
			"values"	=>	["muz","zena"]
		],
		[
			"name"	=>	"styri",
			"rules"	=>	"",
			"title"	=>	"Initial textarea",
			"placeholder"	=>	"",
			"type"	=>	"textarea"
		]
	]
];