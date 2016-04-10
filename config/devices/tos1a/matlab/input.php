<?php

return [
	"start"  =>  [
		[
			"name"	=>	"P",
			"rules"	=>	"required",
			"title"	=>	"P",
			"placeholder"	=>	0.8,
			"type"	=>	"text"
		],
		[
			"name"	=>	"I",
			"rules"	=>	"required",
			"title"	=>	"I",
			"placeholder"	=>	2.95,
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
			"name"	=>	"c_fan",
			"rules"	=>	"required",
			"title"	=>	"Napätie ventilátora",
			"placeholder" =>	20,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_lamp",
			"rules"	=>	"required",
			"title"	=>	"Napätie lampy",
			"placeholder"	=>	50,
			"type"	=>	"text"
		],
		[
			"name"	=>	"c_led",
			"rules"	=>	"required",
			"title"	=>	"Napätie ledky",
			"placeholder" => 0,
			"type"	=>	"text"
		],
		[
			"name"	=>	"ctrltyp",
			"rules"	=>	"required",
			"title"	=>	"Typ simulacie",
			"placeholder"	=>	"NO",
			"type"	=>	"text"
		],
		[
			"name"	=>	"in_sw",
			"rules"	=>	"required",
			"title"	=>	"INSW",
			"placeholder"	=>	3,
			"type"	=>	"text"
		],
		[
			"name"	=>	"out_sw",
			"rules"	=>	"required",
			"title"	=>	"Regulovaná veličina",
			"placeholder"	=>	1,
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
			"title"	=>	"Vzorkovacia frekvencia",
			"placeholder"	=>	200,
			"type"	=>	"text"
		],
		[
			"name"	=>	"input",
			"rules"	=>	"required",
			"title"	=>	"Žiadaná hodnota",
			"placeholder"	=>	30,
			"type"	=>	"text"
		],
		[
			"name"	=>	"scifun",
			"rules"	=>	"required",
			"title"	=>	"Scifun ????",
			"placeholder"	=>	"y1=u1",
			"type"	=>	"textarea"
		]
	]
];