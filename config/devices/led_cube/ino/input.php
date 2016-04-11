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
			"name"	=>	"c_raw",
			"rules"	=>	"required",
			"title"	=>	"Arduino program v C",
			"placeholder"	=>	'void setup() {                
    pinMode(13, OUTPUT);     
}

void loop() {
    digitalWrite(13, HIGH);   // set the LED on
    delay(1000);              // wait for a second
    digitalWrite(13, LOW);    // set the LED off
    delay(1000);              // wait for a second
} 
				',
			"type"	=>	"textarea"
		],
		[
			"name"	=>	"type",
			"rules"	=>	"required",
			"title"	=>	"Jazyk vstupu",
			"placeholder"	=>	'',
			"type"	=>	"radio",
			"values"	=>	["C","JavaScript"]
		]
	]
];