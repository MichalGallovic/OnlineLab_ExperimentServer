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
		],
		[
			"name"	=>	"regulator",
			"rules"	=>	"required",
			"title"	=>	"Regulator",
			"type"	=>	"file"
		]
	]
];