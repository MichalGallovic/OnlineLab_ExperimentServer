<?php

return [
	// "command_name"	=>	[
	// 	[
	// 		"name"	=>	"form_name",
	// 		"rules"	=>	"required",
	// 		"title"	=>	"Form title",
	// 		"placeholder"	=>	0.8 //Default form value,
	// 		"type"	=>	"text"
	// 	],
	// 	[
	// 		"name"	=>	"form_name2",
	// 		"rules"	=>	"required",
	// 		"title"	=>	"Form title2",
	// 		"placeholder"	=>	0.8 //Default form value,
	// 		"type"	=>	"textarea"
	// 	],
	// 		"name"	=>	"form_name3",
	// 		"rules"	=>	"required",
	// 		"title"	=>	"Form title2",
	// 		"placeholder"	=>	0.8 //Default form value,
	// 		"type"	=>	"checkbox",
	// 		"values"	=>	["Prva","Druha","Tretia"]
	// 	]
	// ]
"change"	=>	[
		[
			"name"	=>	"js_raw",
			"rules"	=>	"required",
			"title"	=>	"Arduino program v javascripte",
			"placeholder"	=>	'on(0:7,0:7,0:7);
delay_ms(5000);
off(0:7,0,0);
delay_ms(2000);
off(0,0:7,0);
delay_ms(2000);
off(0,0,0:7);
delay_ms(2000);

on([2,4,8],5,3);
on([2,4,8],7,[3,5]);

on(0,1,2);
on(0,1,3);
delay_ms(1200);
off(0,1,2);
on(7,7,7);',
			"type"	=>	"textarea"
		]
		]
];