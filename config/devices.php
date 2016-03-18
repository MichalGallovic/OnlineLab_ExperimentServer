<?php

return [

	"tos1a" => [
		"softwares"	=>	[
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

		"output" => [
			[
				"name"  => "temp_chip",
				"title" => "Chip temp",
			],
			[
				"name"  => "f_temp_int",
				"title" => "Filtered internal temp",
			],
			[
				"name"  => "d_temp_int",
				"title" => "Derived internal temp",
			],
			[
				"name"  => "f_temp_ext",
				"title" => "Filtered external temp",
			],
			[
				"name"  => "d_temp_ext",
				"title" => "Derived external temp",
			],
			[
				"name"  => "f_light_int_lin",
				"title" => "Light filtered lin intensity",
			],
			[
				"name"  => "d_light_int_lin",
				"title" => "Light derived lin intensity",
			],
			[
				"name"  => "f_light_int_log",
				"title" => "Light filtered log intensity",
			],
			[
				"name"  => "d_light_int_log",
				"title" => "Light derived log intensity",
			],
			[
				"name"  => "I_bulb",
				"title" => "Bulb current",
			],
			[
				"name"  => "V_bulb",
				"title" => "Bulb voltage",
			],
			[
				"name"  => "I_fan",
				"title" => "Fan current",
			],
			[
				"name"  => "V_fan",
				"title" => "Fan voltage",
			],
			[
				"name"  => "f_rpm",
				"title" => "Fan filtered rpm",
			],
			[
				"name"  => "d_rmp",
				"title" => "Fan derived rpm",
			],

		]
		// "output"	=>	[
		// 	"temp_chip",
		// 	"f_temp_int",
		// 	"d_temp_int",
		// 	"f_temp_ext",
		// 	"d_temp_ext",
		// 	"f_light_int_lin",
		// 	"d_light_int_lin",
		// 	"f_light_int_log",
		// 	"d_light_int_log",
		// 	"I_bulb",
		// 	"V_bulb",
		// 	"I_fan",
		// 	"V_fan",
		// 	"f_rpm",
		// 	"d_rmp"
		// ]
	]

];