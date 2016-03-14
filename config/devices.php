<?php

return [

	"tos1a" => [
		"experiments"	=>	[
			"openloop" => [
				"input"	=>	[
					[
						"name"	=>	"c_fan",
						"rules"	=>	"required",
						"title"	=>	"Napätie ventilátora",
						"placeholder"	=>	20
					],
					[
						"name"	=>	"c_lamp",
						"rules"	=>	"required",
						"title"	=>	"Napätie lampy",
						"placeholder"	=>	60
					],
					[
						"name"	=>	"c_led",
						"rules"	=>	"required",
						"title"	=>	"Napätie ledky",
						"placeholder"	=>	0
					],
					[
						"name"	=>	"t_sim",
						"rules"	=>	"required",
						"title"	=>	"Čas simulácie",
						"placeholder"	=>	10
					],
					[
						"name"	=>	"s_rate",
						"rules"	=>	"required",
						"title"	=>	"Vzorkovací čas",
						"placeholder"	=>	200
					]
				]
			],

			"matlab" => [
				"input"	=>	[
					[
						"name"	=>	"P",
						"rules"	=>	"required",
						"title"	=>	"P",
						"placeholder"	=>	0.8
					],
					[
						"name"	=>	"I",
						"rules"	=>	"required",
						"title"	=>	"I",
						"placeholder"	=>	2.95
					],
					[
						"name"	=>	"D",
						"rules"	=>	"required",
						"title"	=>	"D",
						"placeholder"	=>	0
					],
					[
						"name"	=>	"c_fan",
						"rules"	=>	"required",
						"title"	=>	"Napätie ventilátora",
						"placeholder"	=>	20
					],
					[
						"name"	=>	"c_lamp",
						"rules"	=>	"required",
						"title"	=>	"Napätie lampy",
						"placeholder"	=>	50
					],
					[
						"name"	=>	"c_led",
						"rules"	=>	"required",
						"title"	=>	"Napätie ledky",
						"placeholder"	=>	0
					],
					[
						"name"	=>	"ctrltyp",
						"rules"	=>	"required",
						"title"	=>	"Typ simulacie",
						"placeholder"	=>	"NO"
					],
					[
						"name"	=>	"in_sw",
						"rules"	=>	"required",
						"title"	=>	"INSW - asi zbytocne ?",
						"placeholder"	=>	3
					],
					[
						"name"	=>	"out_sw",
						"rules"	=>	"required",
						"title"	=>	"Regulovaná veličina",
						"placeholder"	=>	1
					],
					[
						"name"	=>	"t_sim",
						"rules"	=>	"required",
						"title"	=>	"Čas simulácie",
						"placeholder"	=>	10
					],
					[
						"name"	=>	"s_rate",
						"rules"	=>	"required",
						"title"	=>	"Vzorkovacia frekvencia",
						"placeholder"	=>	200
					],
					[
						"name"	=>	"input",
						"rules"	=>	"required",
						"title"	=>	"Žiadaná hodnota",
						"placeholder"	=>	30
					],
					[
						"name"	=>	"scifun",
						"rules"	=>	"required",
						"title"	=>	"Scifun ????",
						"placeholder"	=>	"y1=u1"
					]
				]
			],
		],

		// "output" => [
		// 	[
		// 		"name"  => "temp_chip",
		// 		"title" => "Chip temperature",
		// 	],
		// 	[
		// 		"name"  => "f_temp_int",
		// 		"title" => "Chip temperature",
		// 	],
		// 	[
		// 		"name"  => "d_temp_ext",
		// 		"title" => "",
		// 	]
		// ]
		"output"	=>	[
			"temp_chip",
			"f_temp_int",
			"d_temp_ext",
			"f_temp_ext",
			"d_temp_ext",
			"f_light_int_lin",
			"d_light_int_lin",
			"f_light_int_log",
			"d_light_int_log",
			"I_bulb",
			"V_bulb",
			"I_fan",
			"V_fan",
			"f_rpm",
			"d_rmp"
		]
	]

];