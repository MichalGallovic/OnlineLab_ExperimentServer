<?php

return [
	// "command_name"	=>	[
	// 	[
	// 		"name"	=>	"form_name",
	// 		"rules"	=>	"required",
	// 		"title"	=>	"Form title",
	// 		"placeholder"	=>	0.8, //Default form value,
	// 		"type"	=>	"text"
	// 	],
	// 	[
	// 		"name"	=>	"form_name2",
	// 		"rules"	=>	"required",
	// 		"title"	=>	"Form title2",
	// 		"placeholder"	=>	0.8, //Default form value,
	// 		"type"	=>	"textarea"
	// 	],
	//	[
	// 		"name"	=>	"form_name3",
	// 		"rules"	=>	"required",
	// 		"title"	=>	"Form title2",
	// 		"placeholder"	=>	0.8, //Default form value,
	// 		"type"	=>	"checkbox",
	// 		"values"	=>	["Prva","Druha","Tretia"]
	// 	]
	// ]
	 "init"	=>	[
	 	[
	 		"name"	=>	"cas_sim",
	 		"rules"	=>	"required",
	 		"title"	=>	"Dlžka trvania experimentu [s]",
	 		"placeholder"	=>	20, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"servo_taz",
	 		"rules"	=>	"required",
	 		"title"	=>	"Poloha závažia (0-180)",
	 		"placeholder"	=>	180, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"reg_typ",
	 		"rules"	=>	"required",
	 		"title"	=>	"Typ regulátora sústavy",
	 		"placeholder"	=>	"PID", //Default form value,
	 		"type"	=>	"radio",
                        "values"=>	["PID","Rovnice","Súbor"]
	 	],
	 	[
	 		"name"	=>	"PID_P",
	 		"rules"	=>	"",
	 		"title"	=>	"P zložka",
	 		"placeholder"	=>	15, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"PID_Ti",
	 		"rules"	=>	"",
	 		"title"	=>	"Časová konštanta Ti",
	 		"placeholder"	=>	0.15, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"PID_Td",
	 		"rules"	=>	"",
	 		"title"	=>	"Časová konštanta Td",
	 		"placeholder"	=>	0.02, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"equations_controller",
	 		"rules"	=>	"",
	 		"title"	=>	"Zápis rovníc regulátora",
	 		"placeholder"	=>	'e=setpoint_phi - phi;'.PHP_EOL. ' actuator_value = -e * P;', //Default form value,
	 		"type"	=>	"textarea"
	 	],
	 	[
	 		"name"	=>	"equations_variables",
	 		"rules"	=>	"",
	 		"title"	=>	"Zápis premenných",
	 		"placeholder"	=>	'Real e(start=0) "control error";', //Default form value,
	 		"type"	=>	"textarea"
	 	],
	 	[
	 		"name"	=>	"equations_parameters",
	 		"rules"	=>	"",
	 		"title"	=>	"Zápis parametrov",
	 		"placeholder"	=>	'parameter Real P=50 "proportional gain P";', //Default form value,
	 		"type"	=>	"textarea"
	 	],
		[
	 		"name"	=>	"file",
	 		"rules"	=>	"",
	 		"title"	=>	"Názov súboru",
	 		"placeholder"	=>	"", //Default form value,
	 		"type"	=>	"select",
                        "values"	=>	["PID","niecoine","uplneine"]
	 	]
	 ],
 	 "start"	=>	[
	 	[
	 		"name"	=>	"s_rate",
	 		"rules"	=>	"required",
	 		"title"	=>	"Perioda vzorkovania pre vysledky",
	 		"placeholder"	=>	0.1, //Default form value,
	 		"type"	=>	"text"
	 	]
	 ],
 	 "change"	=>	[
	 	[
	 		"name"	=>	"user_val1",
	 		"rules"	=>	"required",
	 		"title"	=>	"Žiadaná hodnota (ref_val)",
	 		"placeholder"	=>	0.0, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"user_val2",
	 		"rules"	=>	"required",
	 		"title"	=>	"Parameter user_val2",
	 		"placeholder"	=>	0.0, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"user_val3",
	 		"rules"	=>	"required",
	 		"title"	=>	"Parameter user_val3",
	 		"placeholder"	=>	0.0, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"user_val4",
	 		"rules"	=>	"required",
	 		"title"	=>	"Parameter user_val4",
	 		"placeholder"	=>	0.0, //Default form value,
	 		"type"	=>	"text"
	 	],
	 	[
	 		"name"	=>	"user_val5",
	 		"rules"	=>	"required",
	 		"title"	=>	"Parameter user_val5",
	 		"placeholder"	=>	0.0, //Default form value,
	 		"type"	=>	"text"
	 	],
	 ]
];
