<?php

return [

	"tos1a" => [
		"experiments"	=>	[
			"openloop" => [
				"input"	=>	[
					"c_fan" => "required",
					"c_lamp" => "required",
					"c_led" => "required",
					"t_sim" => "required",
					"s_rate" => "required"
				]
			],

			"matlab" => [
				"input"	=>	[
					"P" => "required",
					"I" => "required",
					"D" => "required",
					"c_fan" => "required",
					"c_lamp" => "required",
					"c_led" => "required",
					"in_sw" => "required",
					"out_sw" => "required",
					"t_sim" => "required",
					"s_rate" => "required",
					"input" => "required",
					"scifun" => "required"
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