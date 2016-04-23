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
			"name"	=>	"type",
			"rules"	=>	"required",
			"title"	=>	"Jazyk vstupu",
			"placeholder"	=>	'',
			"type"	=>	"radio",
			"values"	=>	["C","JavaScript"]
		],
		[
			"name"	=>	"c_raw",
			"rules"	=>	"required",
			"title"	=>	"Arduino program v C",
			"placeholder"	=>	'
// Set a single voxel to ON
void setvoxel(int x, int y, int z)
{
 if (inrange(x,y,z))
   cube[z][y] |= (1 << x);
}


// Set a single voxel to OFF
void clrvoxel(int x, int y, int z)
{
 if (inrange(x,y,z))
   cube[z][y] &= ~(1 << x);
}
				',
			"type"	=>	"textarea"
<<<<<<< HEAD:config/devices/led_cube/c/input.php
=======
		],
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
>>>>>>> be798a06715dc332cc3ff4e83231de5a09cac7a6:config/devices/led_cube/ino/input.php
		]
	]
];