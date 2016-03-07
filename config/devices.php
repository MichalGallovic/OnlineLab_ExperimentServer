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
						"title"	=>	"P"
					],
					[
						"name"	=>	"I",
						"rules"	=>	"required",
						"title"	=>	"I"
					],
					[
						"name"	=>	"D",
						"rules"	=>	"required",
						"title"	=>	"D"
					],
					[
						"name"	=>	"c_fan",
						"rules"	=>	"required",
						"title"	=>	"Napätie ventilátora"
					],
					[
						"name"	=>	"c_lamp",
						"rules"	=>	"required",
						"title"	=>	"Napätie lampy"
					],
					[
						"name"	=>	"c_led",
						"rules"	=>	"required",
						"title"	=>	"Napätie ledky"
					],
					[
						"name"	=>	"ctrltyp",
						"rules"	=>	"required",
						"title"	=>	"Typ simulacie"
					],
					[
						"name"	=>	"in_sw",
						"rules"	=>	"required",
						"title"	=>	"INSW - asi zbytocne ?"
					],
					[
						"name"	=>	"out_sw",
						"rules"	=>	"required",
						"title"	=>	"Regulovaná veličina"
					],
					[
						"name"	=>	"t_sim",
						"rules"	=>	"required",
						"title"	=>	"Čas simulácie"
					],
					[
						"name"	=>	"s_rate",
						"rules"	=>	"required",
						"title"	=>	"Vzorkovacia frekvencia"
					],
					[
						"name"	=>	"input",
						"rules"	=>	"required",
						"title"	=>	"Žiadaná hodnota"
					],
					[
						"name"	=>	"scifun",
						"rules"	=>	"required",
						"title"	=>	"Scifun ????"
					]
				]
			],
		],

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