<?php

return [

	"tos1a" => [
		"experiments"	=>	[
			"openloop" => [
				"input"	=>	[
					"c_fan" => [
						"rules"	=>	"required",
						"name"	=>	"Napätie ventilátora"
					],
					"c_lamp" => [
						"rules"	=>	"required",
						"name"	=>	"Napätie lampy"
					],
					"c_led" => [
						"rules"	=>	"required",
						"name"	=>	"Napätie ledky"
					],
					"t_sim" => [
						"rules"	=>	"required",
						"name"	=>	"Čas simulácie"
					],
					"s_rate" => [
						"rules"	=>	"required",
						"name"	=>	"Vzorkovací "
					]
				]
			],

			"matlab" => [
				"input"	=>	[
					"P" => [
						"rules"	=>	"required",
						"name"	=>	"P"
					],
					"I" => [
						"rules"	=>	"required",
						"name"	=>	"I"
					],
					"D" => [
						"rules"	=>	"required",
						"name"	=>	"D"
					],
					"c_fan" => [
						"rules"	=>	"required",
						"name"	=>	"Napätie ventilátora"
					],
					"c_lamp" => [
						"rules"	=>	"required",
						"name"	=>	"Napätie lampy"
					],
					"c_led" => [
						"rules"	=>	"required",
						"name"	=>	"Napätie ledky"
					],
					"in_sw" => [
						"rules"	=>	"required",
						"name"	=>	"INSW - asi zbytocne ?"
					],
					"out_sw" => [
						"rules"	=>	"required",
						"name"	=>	"Regulovaná veličina"
					],
					"t_sim" => [
						"rules"	=>	"required",
						"name"	=>	"Čas simulácie"
					],
					"s_rate" => [
						"rules"	=>	"required",
						"name"	=>	"Vzorkovacia frekvencia"
					],
					"input" => [
						"rules"	=>	"required",
						"name"	=>	"Žiadaná hodnota"
					],
					"scifun" => [
						"rules"	=>	"required",
						"name"	=>	"Scifun ????"
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