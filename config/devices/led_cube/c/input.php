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
			"placeholder"	=>	'
// DEMO EXAMPLES
effect_planboing(AXIS_Z, 4700);
effect_planboing(AXIS_Y, 4700);
effect_planboing(AXIS_X, 4700);

effect_blinky2();

effect_random_filler(75,1);

effect_rain(200);

effect_boxside_randsend_parallel (AXIS_X, 0, 150, 1);
effect_boxside_randsend_parallel (AXIS_Y, 0, 150, 1);
effect_boxside_randsend_parallel (AXIS_Z, 0, 150, 1);

turning_cross(300);

effect_intro();

zoom_pyramid();
zoom_pyramid_clear();

firework(-2, -2, 50);

pyro(); pyro();

space(100); space(100);

firework(-2, 1, 600);

for (cnt = 0; cnt < 501; cnt += 100)
  turning_cross_animation(cnt);
for (cnt = 500; cnt >= 0; cnt -= 100)
  turning_cross_animation(cnt);

turning_cross(300);

syd_rox();

effect_planboing(AXIS_Z, 4700);
effect_planboing(AXIS_Y, 4700);
effect_planboing(AXIS_X, 4700);

effect_blinky2();

effect_random_filler(75, 1);
effect_random_filler(75, 0);

effect_rain(200);

effect_boxside_randsend_parallel(AXIS_X, 0, 150, 1);
effect_boxside_randsend_parallel(AXIS_X, 1, 150, 1);
effect_boxside_randsend_parallel(AXIS_Y, 0, 150, 1);
effect_boxside_randsend_parallel(AXIS_Y, 1, 150, 1);
effect_boxside_randsend_parallel(AXIS_Z, 0, 150, 1);
effect_boxside_randsend_parallel(AXIS_Z, 1, 150, 1);

turning_cross(300);

effect_intro();

zoom_pyramid();
zoom_pyramid_clear();
zoom_pyramid();
zoom_pyramid_clear();

firework(0, 0, 0);
firework(-2, -2, 50);
firework(1, 1, -250);
firework(0, 1, 200);
firework(1, -3, 400);
firework(2, -3, 600);
firework(2, 1, 500);
firework(2, -2, 200);
firework(2, 1, 0);
firework(0, 0, 0);

pyro();
pyro();

firework(2, -2, 500);

space(100);
space(100);

firework(-2, 1, 600);

for (cnt = 0; cnt < 501; cnt += 100)
    turning_cross_animation(cnt);
for (cnt = 500; cnt >= 0; cnt -= 100)
    turning_cross_animation(cnt);

turning_cross(300);

syd_rox();',
			"type"	=>	"textarea"
		]
	]
];