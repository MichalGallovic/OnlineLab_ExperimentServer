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
			"title"	=>	"Arduino program v JS",
			"placeholder"	=>	'
on(1,1,1);
on([2,4,8],8,1);
off(1:5,1:5,1,2);
				',
			"type"	=>	"textarea"
		]
	]
];