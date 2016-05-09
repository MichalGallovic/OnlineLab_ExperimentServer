<?php

return [
	"start"  =>  [
		[
			"name"	=>	"required_value",
			"rules"	=>	"required",
			"title"	=>	"Required value  °C/lx/RPM",
			"placeholder"	=>	30,
			"type"	=>	"text"
		],
	 	[
	 		"name"	=>	"out_sw",
	 		"rules"	=>	"required",
	 		"title"	=>	"Controlled variable",
	 		"placeholder"	=>	0, 
	 		"type"	=>	"select",
	 		"values"	=>	["Temperature","Light Intensity","Fan RPM"]
	 	],	
	 	[
	 		"name"	=>	"in_sw",
	 		"rules"	=>	"required",
	 		"title"	=>	"Control variable",
	 		"placeholder"	=>	0, 
	 		"type"	=>	"select",
	 		"values"	=>	["Bulb","Led","Fan"]
	 	],		
		[
			"name"	=>	"c_lamp",
			"rules"	=>	"required",
			"title"	=>	"Voltage of Bulb (0-100%)",
			"placeholder"	=>	0,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_led",
			"rules"	=>	"required",
			"title"	=>	"Voltage of Led (0-100%)",
			"placeholder"	=>	0,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Voltage of Fan (0-100%)",
			"placeholder"	=>	0,
			"type"	=>	"text"
		],		
		[
			"name"	=>	"time",
			"rules"	=>	"required",
			"title"	=>	"Simulation duration in s",
			"placeholder"	=>	10,
			"type"	=>	"text"
		],
		[
			"name"	=>	"ts",
			"rules"	=>	"required",
			"title"	=>	"TS Sampling rate in ms",
			"placeholder"	=>	200,
			"type"	=>	"text"
		],
		[
	 		"name"	=>	"own_ctrl",
	 		"rules"	=>	"required",
	 		"title"	=>	"Type of regulator",
	 		"placeholder"	=>	0, 
	 		"type"	=>	"radio",
	 		"values"	=>	["PID","Own function"]
	 	],
	 	[
			"name"	=>	"P",
			"rules"	=>	"required",
			"title"	=>	"P",
			"placeholder"	=>	0.1,
			"type"	=>	"text"
		],
		[
			"name"	=>	"I",
			"rules"	=>	"required",
			"title"	=>	"I",
			"placeholder"	=>	1.5,
			"type"	=>	"text"
		],
		[
			"name"	=>	"D",
			"rules"	=>	"required",
			"title"	=>	"D",
			"placeholder"	=>	0,
			"type"	=>	"text"
		],
		[
			"name"	=>	"uploaded_file",
			"rules"	=>	"",
			"title"	=>	"File to upload",
			"type"	=>	"file"
		],
		[
	 		"name"	=>	"user_function",
			"rules"	=>	"",
	 		"title"	=>	"Own function - in format y1=(relation of four inputs u1,u2,u3,u4,Ts) u1-required value, u2-temperature, u3-light ntensity, u4-fan rpm, Ts-sample rate",
	 		"placeholder"	=>	"y1=u1", 
	 		"type"	=>	"textarea"
	 	]


	],

	"change"  =>  [
		[
			"name"	=>	"required_value",
			"rules"	=>	"",
			"title"	=>	"Required value  °C/lx/RPM",
			"placeholder"	=>	"",
			"type"	=>	"text"
		],
		[
			"name"	=>	"P",
			"rules"	=>	"",
			"title"	=>	"P",
			"placeholder"	=>	"",
			"type"	=>	"text"
		],
		[
			"name"	=>	"I",
			"rules"	=>	"",
			"title"	=>	"I",
			"placeholder"	=>	"",
			"type"	=>	"text"
		],
		[
			"name"	=>	"D",
			"rules"	=>	"",
			"title"	=>	"D",
			"placeholder"	=>	"",
			"type"	=>	"text"
		]
	]

];