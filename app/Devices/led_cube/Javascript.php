<?php

namespace App\Devices\led_cube;

use App\Device;
use App\Experiment;
use App\Devices\AbstractDevice;
use App\Devices\Traits\AsyncRunnable;
use App\Devices\Contracts\DeviceDriverContract;

class Javascript extends AbstractDevice implements DeviceDriverContract {


	/**
     * Paths to read/stop/run scripts relative to
     * $(app_root)/server_scripts folder
     * @var array
     */
	protected $scriptPaths = [
        "read"	=> "",
        "stop"  => "",
        "start"	=> "",
        "init"  => "",
        "change"=> "led_cube/c/"
    ];


    /**
     * Construct base class (App\Devices\AbstractDevice)
     * @param Device     $device     Device model from DB
     * @param Experiment $experiment Experiment model from DB
     */
	public function __construct(Device $device, Experiment $experiment)
	{
		parent::__construct($device,$experiment);
	}

    protected function change($input){

$code_init = '
#include <avr/interrupt.h>
#include <string.h>
#define AXIS_X 1
#define AXIS_Y 2
#define AXIS_Z 3

volatile unsigned char cube[8][8];
volatile int current_layer = 0;

void setup(){
  int i;
  
  for(i=0; i<14; i++)
    pinMode(i, OUTPUT);
  
  // pinMode(A0, OUTPUT) as specified in the arduino reference didnt work. So I accessed the registers directly.
  DDRC = 0xff;
  PORTC = 0x00;
  
  // Reset any PWM configuration that the arduino may have set up automagically!
  TCCR2A = 0x00;
  TCCR2B = 0x00;

  TCCR2A |= (0x01 << WGM21); // CTC mode. clear counter on TCNT2 == OCR2A
  OCR2A = 10; // Interrupt every 25600th cpu cycle (256*100)
  TCNT2 = 0x00; // start counting at 0
  TCCR2B |= (0x01 << CS22) | (0x01 << CS21); // Start the clock with a 256 prescaler
  
  TIMSK2 |= (0x01 << OCIE2A);
}

ISR (TIMER2_COMPA_vect)
{
  int i;
  
  // all layer selects off
  PORTC = 0x00;
  PORTB &= 0x0f;
  
  PORTB |= 0x08; // output enable off.
  
  for (i=0; i<8; i++)
  {
    PORTD = cube[current_layer][i];
    PORTB = (PORTB & 0xF8) | (0x07 & (i+1));
  }
  
  PORTB &= 0b00110111; // Output enable on.
  
  if (current_layer < 6)
  {
    PORTC = (0x01 << current_layer);
  } else if (current_layer == 6)
  {
    digitalWrite(12, HIGH);
  } else
  {
    digitalWrite(13, HIGH);
  }
  
  current_layer++;
  
  if (current_layer == 8)
    current_layer = 0;
}

void loop()
{
  int i,x,y,z;
  int cnt;

  while (true){
  ';


$code_end = '

  }
}



// ==========================================================================================
//   Effect functions
// ==========================================================================================

void draw_positions_axis (char axis, unsigned char positions[64], int invert)
{
  int x, y, p;
  
  fill(0x00);
  
  for (x=0; x<8; x++)
  {
    for (y=0; y<8; y++)
    {
      if (invert)
      {
        p = (7-positions[(x*8)+y]);
      } else
      {
        p = positions[(x*8)+y];
      }
    
      if (axis == AXIS_Z)
        setvoxel(x,y,p);
        
      if (axis == AXIS_Y)
        setvoxel(x,p,y);
        
      if (axis == AXIS_X)
        setvoxel(p,y,x);
    }
  }
  
}


void effect_boxside_randsend_parallel (char axis, int origin, int delay, int mode)
{
  int i;
  int done;
  unsigned char cubepos[64];
  unsigned char pos[64];
  int notdone = 1;
  int notdone2 = 1;
  int sent = 0;
  
  for (i=0;i<64;i++)
  {
    pos[i] = 0;
  }
  
  while (notdone)
  {
    if (mode == 1)
    {
      notdone2 = 1;
      while (notdone2 && sent<64)
      {
        i = rand()%64;
        if (pos[i] == 0)
        {
          sent++;
          pos[i] += 1;
          notdone2 = 0;
        }
      }
    } else if (mode == 2)
    {
      if (sent<64)
      {
        pos[sent] += 1;
        sent++;
      }
    }
    
    done = 0;
    for (i=0;i<64;i++)
    {
      if (pos[i] > 0 && pos[i] <7)
      {
        pos[i] += 1;
      }
        
      if (pos[i] == 7)
        done++;
    }
    
    if (done == 64)
      notdone = 0;
    
    for (i=0;i<64;i++)
    {
      if (origin == 0)
      {
        cubepos[i] = pos[i];
      } else
      {
        cubepos[i] = (7-pos[i]);
      }
    }
    
    
    delay_ms(delay);
    draw_positions_axis(axis,cubepos,0);

  }
  
}


void effect_rain (int iterations)
{
  int i, ii;
  int rnd_x;
  int rnd_y;
  int rnd_num;
  
  for (ii=0;ii<iterations;ii++)
  {
    rnd_num = rand()%4;
    
    for (i=0; i < rnd_num;i++)
    {
      rnd_x = rand()%8;
      rnd_y = rand()%8;
      setvoxel(rnd_x,rnd_y,7);
    }
    
    delay_ms(1000);
    shift(AXIS_Z,-1);
  }
}

// Set or clear exactly 512 voxels in a random order.
void effect_random_filler (int delay, int state)
{
  int x,y,z;
  int loop = 0;
  
  
  if (state == 1)
  {
    fill(0x00);
  } else
  {
    fill(0xff);
  }
  
  while (loop<511)
  {
    x = rand()%8;
    y = rand()%8;
    z = rand()%8;

    if ((state == 0 && getvoxel(x,y,z) == 0x01) || (state == 1 && getvoxel(x,y,z) == 0x00))
    {
      altervoxel(x,y,z,state);
      delay_ms(delay);
      loop++;
    } 
  }
}


void effect_blinky2()
{
  int i,r;
  fill(0x00);
  
  for (r=0;r<2;r++)
  {
    i = 750;
    while (i>0)
    {
      fill(0x00);
      delay_ms(i);
      
      fill(0xff);
      delay_ms(100);
      
      i = i - (15+(1000/(i/10)));
    }
    
    delay_ms(1000);
    
    i = 750;
    while (i>0)
    {
      fill(0x00);
      delay_ms(751-i);
      
      fill(0xff);
      delay_ms(100);
      
      i = i - (15+(1000/(i/10)));
    }
  }

}

// Draw a plane on one axis and send it back and forth once.
void effect_planboing (int plane, int speed)
{
  int i;
  for (i=0;i<8;i++){
    fill(0x00);
        setplane(plane, i);
    delay_ms(speed);
  }
  
  for (i=7;i>=0;i--){
    fill(0x00);
        setplane(plane,i);
    delay_ms(speed);
  }
}

void turning_cross_animation(int time) {
  int i, j, k;

  fill(0x00);

  //Cross

  //1
  for (i = 0; i < 8; i++) {
    setvoxel(7, 3, i);
    setvoxel(7, 4, i);
    setvoxel(7, i, 3);
    setvoxel(7, i, 4);
  }

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //2
  setvoxel(6, 7, 5);
  setvoxel(6, 6, 5);
  clrvoxel(6, 7, 3);
  clrvoxel(6, 6, 3);

  setvoxel(6, 2, 7);
  setvoxel(6, 2, 6);
  clrvoxel(6, 4, 7);
  clrvoxel(6, 4, 6);

  setvoxel(6, 0, 2);
  setvoxel(6, 1, 2);
  clrvoxel(6, 0, 4);
  clrvoxel(6, 1, 4);

  setvoxel(6, 5, 0);
  setvoxel(6, 5, 1);
  clrvoxel(6, 3, 0);
  clrvoxel(6, 3, 1);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //3
  setvoxel(5, 6, 6);
  setvoxel(5, 5, 5);
  clrvoxel(5, 7, 4);
  clrvoxel(5, 6, 4);

  setvoxel(5, 1, 6);
  setvoxel(5, 2, 5);
  clrvoxel(5, 3, 7);
  clrvoxel(5, 3, 6);

  setvoxel(5, 2, 2);
  setvoxel(5, 1, 1);
  clrvoxel(5, 0, 3);
  clrvoxel(5, 1, 3);

  setvoxel(5, 6, 1);
  setvoxel(5, 5, 2);
  clrvoxel(5, 4, 0);
  clrvoxel(5, 4, 1);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //4
  //Corners
  setvoxel(4, 7, 7);
  setvoxel(4, 7, 0);
  setvoxel(4, 0, 7);
  setvoxel(4, 0, 0);

  setvoxel(4, 6, 7);
  setvoxel(4, 5, 6);
  clrvoxel(4, 7, 5);
  clrvoxel(4, 6, 5);

  setvoxel(4, 0, 6);
  setvoxel(4, 1, 5);
  clrvoxel(4, 2, 7);
  clrvoxel(4, 2, 6);

  setvoxel(4, 1, 0);
  setvoxel(4, 2, 1);
  clrvoxel(4, 0, 2);
  clrvoxel(4, 1, 2);

  setvoxel(4, 7, 1);
  setvoxel(4, 6, 2);
  clrvoxel(4, 5, 0);
  clrvoxel(4, 5, 1);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //5
  //Corners
  clrvoxel(3, 7, 7);
  clrvoxel(3, 7, 0);
  clrvoxel(3, 0, 7);
  clrvoxel(3, 0, 0);

  setvoxel(3, 7, 2);
  setvoxel(3, 6, 3);
  clrvoxel(3, 6, 1);
  clrvoxel(3, 5, 2);

  setvoxel(3, 0, 5);
  setvoxel(3, 1, 4);
  setvoxel(3, 4, 5);
  clrvoxel(3, 1, 6);
  clrvoxel(3, 2, 5);

  setvoxel(3, 3, 1);
  setvoxel(3, 2, 0);
  clrvoxel(3, 1, 1);
  clrvoxel(3, 2, 2);

  setvoxel(3, 4, 6);
  setvoxel(3, 5, 7);
  clrvoxel(3, 6, 6);
  clrvoxel(3, 5, 5);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //6
  setvoxel(2, 7, 3);
  setvoxel(2, 6, 4);
  clrvoxel(2, 7, 1);
  clrvoxel(2, 6, 2);

  setvoxel(2, 0, 5);
  setvoxel(2, 1, 4);
  clrvoxel(2, 0, 6);
  clrvoxel(2, 1, 5);

  setvoxel(2, 0, 4);
  setvoxel(2, 1, 3);
  clrvoxel(2, 1, 6);
  clrvoxel(2, 2, 5);

  setvoxel(2, 3, 6);
  setvoxel(2, 4, 7);
  clrvoxel(2, 5, 6);
  clrvoxel(2, 6, 7);

  setvoxel(2, 4, 1);
  setvoxel(2, 3, 0);
  clrvoxel(2, 1, 0);
  clrvoxel(2, 2, 1);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //7

  setvoxel(1, 7, 4);
  clrvoxel(1, 7, 2);

  setvoxel(1, 0, 4);
  clrvoxel(1, 0, 2);

  setvoxel(1, 0, 3);
  clrvoxel(1, 0, 5);

  setvoxel(1, 3, 7);
  clrvoxel(1, 5, 7);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

  //8
  setvoxel(0, 7, 5);
  setvoxel(0, 6, 5);
  clrvoxel(0, 7, 3);
  clrvoxel(0, 6, 3);

  setvoxel(0, 2, 7);
  setvoxel(0, 2, 6);
  clrvoxel(0, 4, 7);
  clrvoxel(0, 4, 6);

  setvoxel(0, 0, 2);
  setvoxel(0, 1, 2);
  clrvoxel(0, 0, 4);
  clrvoxel(0, 1, 4);

  setvoxel(0, 5, 0);
  setvoxel(0, 5, 1);
  clrvoxel(0, 3, 0);
  clrvoxel(0, 3, 1);

  shift(AXIS_X, -1);
  delay_ms(1000 - time);

}


void turning_cross(int time) {
  int i, j, k;

  fill(0x00);

  //Cross

  //1
  for (i = 0; i < 8; i++) {
    setvoxel(0, 3, i);
    setvoxel(0, 4, i);
    setvoxel(0, i, 3);
    setvoxel(0, i, 4);
  }

  delay_ms(1000 - time);

  //2
  setvoxel(0, 7, 5);
  setvoxel(0, 6, 5);
  clrvoxel(0, 7, 3);
  clrvoxel(0, 6, 3);

  setvoxel(0, 2, 7);
  setvoxel(0, 2, 6);
  clrvoxel(0, 4, 7);
  clrvoxel(0, 4, 6);

  setvoxel(0, 0, 2);
  setvoxel(0, 1, 2);
  clrvoxel(0, 0, 4);
  clrvoxel(0, 1, 4);

  setvoxel(0, 5, 0);
  setvoxel(0, 5, 1);
  clrvoxel(0, 3, 0);
  clrvoxel(0, 3, 1);

  delay_ms(1000 - time);

  //3
  setvoxel(0, 6, 6);
  setvoxel(0, 5, 5);
  clrvoxel(0, 7, 4);
  clrvoxel(0, 6, 4);

  setvoxel(0, 1, 6);
  setvoxel(0, 2, 5);
  clrvoxel(0, 3, 7);
  clrvoxel(0, 3, 6);

  setvoxel(0, 2, 2);
  setvoxel(0, 1, 1);
  clrvoxel(0, 0, 3);
  clrvoxel(0, 1, 3);

  setvoxel(0, 6, 1);
  setvoxel(0, 5, 2);
  clrvoxel(0, 4, 0);
  clrvoxel(0, 4, 1);

  delay_ms(1000 - time);

  //4
  //Corners
  setvoxel(0, 7, 7);
  setvoxel(0, 7, 0);
  setvoxel(0, 0, 7);
  setvoxel(0, 0, 0);

  setvoxel(0, 6, 7);
  setvoxel(0, 5, 6);
  clrvoxel(0, 7, 5);
  clrvoxel(0, 6, 5);

  setvoxel(0, 0, 6);
  setvoxel(0, 1, 5);
  clrvoxel(0, 2, 7);
  clrvoxel(0, 2, 6);

  setvoxel(0, 1, 0);
  setvoxel(0, 2, 1);
  clrvoxel(0, 0, 2);
  clrvoxel(0, 1, 2);

  setvoxel(0, 7, 1);
  setvoxel(0, 6, 2);
  clrvoxel(0, 5, 0);
  clrvoxel(0, 5, 1);

  delay_ms(1000 - time);

  //5
  //Corners
  clrvoxel(0, 7, 7);
  clrvoxel(0, 7, 0);
  clrvoxel(0, 0, 7);
  clrvoxel(0, 0, 0);

  setvoxel(0, 7, 2);
  setvoxel(0, 6, 3);
  clrvoxel(0, 6, 1);
  clrvoxel(0, 5, 2);

  setvoxel(0, 0, 5);
  setvoxel(0, 1, 4);
  setvoxel(0, 4, 5);
  clrvoxel(0, 1, 6);
  clrvoxel(0, 2, 5);

  setvoxel(0, 3, 1);
  setvoxel(0, 2, 0);
  clrvoxel(0, 1, 1);
  clrvoxel(0, 2, 2);

  setvoxel(0, 4, 6);
  setvoxel(0, 5, 7);
  clrvoxel(0, 6, 6);
  clrvoxel(0, 5, 5);

  delay_ms(1000 - time);

  //6
  setvoxel(0, 7, 3);
  setvoxel(0, 6, 4);
  clrvoxel(0, 7, 1);
  clrvoxel(0, 6, 2);

  setvoxel(0, 0, 5);
  setvoxel(0, 1, 4);
  clrvoxel(0, 0, 6);
  clrvoxel(0, 1, 5);

  setvoxel(0, 0, 4);
  setvoxel(0, 1, 3);
  clrvoxel(0, 1, 6);
  clrvoxel(0, 2, 5);

  setvoxel(0, 3, 6);
  setvoxel(0, 4, 7);
  clrvoxel(0, 5, 6);
  clrvoxel(0, 6, 7);

  setvoxel(0, 4, 1);
  setvoxel(0, 3, 0);
  clrvoxel(0, 1, 0);
  clrvoxel(0, 2, 1);

  delay_ms(1000 - time);

  //7

  setvoxel(0, 7, 4);
  clrvoxel(0, 7, 2);

  setvoxel(0, 0, 4);
  clrvoxel(0, 0, 2);

  setvoxel(0, 0, 3);
  clrvoxel(0, 0, 5);

  setvoxel(0, 3, 7);
  clrvoxel(0, 5, 7);

}

void space(int iterations) {

  int i, ii;
  int rnd_y;
  int rnd_z;
  int rnd_num;
  int time;

  time = 700;

  for (ii = 0; ii < iterations; ii++)
  {
    time = time - (iterations / 15);
    rnd_num = rand() % 4;

    for (i = 0; i < rnd_num; i++)
    {
      rnd_y = rand() % 8;
      rnd_z = rand() % 8;
      setvoxel(7, rnd_y, rnd_z);
    }

    delay_ms(time);
    shift(AXIS_X, -1);
  }

  for (ii = 0; ii < iterations; ii++)
  {
    time = time + (iterations / 15);
    rnd_num = rand() % 4;

    for (i = 0; i < rnd_num; i++)
    {
      rnd_y = rand() % 8;
      rnd_z = rand() % 8;
      setvoxel(7, rnd_y, rnd_z);
    }

    delay_ms(time);
    shift(AXIS_X, -1);
  }

}

void syd_rox() {

  fill(0x00);

  //S
  setvoxel(0, 7, 7);
  setvoxel(0, 6, 7);
  setvoxel(0, 7, 6);
  setvoxel(0, 7, 5);
  setvoxel(0, 6, 5);
  setvoxel(0, 6, 4);
  setvoxel(0, 6, 3);
  setvoxel(0, 7, 3);

  //Y
  setvoxel(0, 4, 7);
  setvoxel(0, 4, 6);
  setvoxel(0, 4, 5);
  setvoxel(0, 3, 7);
  setvoxel(0, 3, 6);
  setvoxel(0, 3, 5);
  setvoxel(0, 3, 4);
  setvoxel(0, 3, 3);
  setvoxel(0, 4, 3);

  //D
  setvoxel(0, 1, 7);
  setvoxel(0, 1, 6);
  setvoxel(0, 1, 5);
  setvoxel(0, 1, 4);
  setvoxel(0, 1, 3);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 4);

  delay_ms(15000);
  fill(0x00);

  //R
  setvoxel(0, 7, 7);
  setvoxel(0, 7, 6);
  setvoxel(0, 7, 5);
  setvoxel(0, 7, 4);
  setvoxel(0, 7, 3);
  setvoxel(0, 6, 7);
  setvoxel(0, 5, 7);
  setvoxel(0, 5, 6);
  setvoxel(0, 5, 5);
  setvoxel(0, 6, 5);
  setvoxel(0, 6, 4);
  setvoxel(0, 5, 3);

  //0
  setvoxel(0, 4, 7);
  setvoxel(0, 4, 6);
  setvoxel(0, 4, 5);
  setvoxel(0, 4, 4);
  setvoxel(0, 4, 3);
  setvoxel(0, 3, 7);
  setvoxel(0, 3, 6);
  setvoxel(0, 3, 5);
  setvoxel(0, 3, 4);
  setvoxel(0, 3, 3);

  //X
  setvoxel(0, 2, 7);
  setvoxel(0, 2, 6);
  setvoxel(0, 2, 4);
  setvoxel(0, 2, 3);
  setvoxel(0, 1, 5);
  setvoxel(0, 0, 7);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 3);


  delay_ms(15000);

}

void pyro() {

  fill(0x00);
  //P
  setvoxel(0, 0, 0);
  setvoxel(0, 0, 1);
  setvoxel(0, 0, 2);
  setvoxel(0, 0, 3);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 7);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 7);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 7);
  setvoxel(0, 0, 7);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 4);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  shift(AXIS_Y, 1);
  delay_ms(5000);

  //y
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 7);
  setvoxel(0, 0, 0);

  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 4);
  setvoxel(0, 0, 0);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 7);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 3);
  setvoxel(0, 0, 2);
  setvoxel(0, 0, 1);
  setvoxel(0, 0, 0);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  shift(AXIS_Y, 1);
  delay_ms(5000);

  //r
  setvoxel(0, 0, 0);
  setvoxel(0, 0, 1);
  setvoxel(0, 0, 2);
  setvoxel(0, 0, 3);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 7);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 7);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 3);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 7);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 2);
  setvoxel(0, 0, 1);
  setvoxel(0, 0, 0);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  shift(AXIS_Y, 1);
  delay_ms(5000);

  //0
  setvoxel(0, 0, 0);
  setvoxel(0, 0, 1);
  setvoxel(0, 0, 2);
  setvoxel(0, 0, 3);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 7);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 7);
  setvoxel(0, 0, 0);
  shift(AXIS_Y, 1);
  delay_ms(4000);

  setvoxel(0, 0, 7);
  setvoxel(0, 0, 6);
  setvoxel(0, 0, 5);
  setvoxel(0, 0, 4);
  setvoxel(0, 0, 3);
  setvoxel(0, 0, 2);
  setvoxel(0, 0, 1);
  setvoxel(0, 0, 0);
  shift(AXIS_Y, 1);
  delay_ms(4000);


  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(4000);
  shift(AXIS_Y, 1);
  delay_ms(2500);

  fill(0x00);

}

void firework(int i, int j, int time) {

  fill(0x00);

  setvoxel(3 - i, 4 - j, 0);
  delay_ms(900 - time);

  clrvoxel(3 - i, 4 - j, 0);
  setvoxel(4 - i, 4 - j, 1);
  delay_ms(1200 - time);

  clrvoxel(4 - i, 4 - j, 1);
  setvoxel(4 - i, 5 - j, 2);
  delay_ms(1400 - time);

  clrvoxel(4 - i, 5 - j, 2);
  setvoxel(3 - i, 5 - j, 3);
  delay_ms(1700 - time);

  clrvoxel(3 - i, 5 - j, 3);
  setvoxel(3 - i, 4 - j, 4);
  delay_ms(2000 - time);

  clrvoxel(3 - i, 4 - j, 4);
  setvoxel(4 - i, 4 - j, 5);
  delay_ms(2000 - time);

  clrvoxel(4 - i, 4 - j, 5);
  setvoxel(4 - i, 3 - j, 6);
  delay_ms(2000 - time);

  //Explode
  clrvoxel(4 - i, 3 - j, 6);
  setvoxel(4 - i, 3 - j, 7);
  setvoxel(4 - i, 4 - j, 6);
  setvoxel(4 - i, 2 - j, 6);
  setvoxel(3 - i, 3 - j, 6);
  setvoxel(5 - i, 3 - j, 6);
  delay_ms(2000 - time);

  shift(AXIS_Z, -1);
  setvoxel(4 - i, 5 - j, 5);
  setvoxel(4 - i, 1 - j, 5);
  setvoxel(2 - i, 3 - j, 5);
  setvoxel(6 - i, 3 - j, 5);
  delay_ms(900 - time);

  shift(AXIS_Z, -1);
  setvoxel(4 - i, 6 - j, 3);
  setvoxel(4 - i, 0 - j, 3);
  setvoxel(1 - i, 3 - j, 3);
  setvoxel(7 - i, 3 - j, 3);
  delay_ms(900 - time);

  shift(AXIS_Z, -1);
  setvoxel(4 - i, 7 - j, 1);
  setvoxel(3 - i, 0 - j, 1);
  setvoxel(0 - i, 3 - j, 1);
  setvoxel(7 - i, 2 - j, 1);
  delay_ms(1400 - time);

  shift(AXIS_Z, -1);
  delay_ms(1400 - time);

  shift(AXIS_Z, -1);
  delay_ms(1400 - time);

  shift(AXIS_Z, -1);
  delay_ms(1400 - time);

  shift(AXIS_Z, -1);
  delay_ms(700 - time);

  fill(0x00);

}

void zoom_pyramid_clear() {


  //1

  box_walls(0, 0, 0, 7, 0, 7);
  delay_ms(500);

  //2

  //Pyramid
  box_wireframe(0, 0, 0, 7, 0, 1);

  clrplane_y(0);
  delay_ms(500);

  //3

  //Pyramid
  clrplane_y(1);
  box_walls(0, 2, 0, 7, 2, 7);
  delay_ms(500);

  //4

  //Pyramid
  clrplane_y(2);
  box_walls(0, 3, 0, 7, 3, 7);
  delay_ms(500);

  //5

  //Pyramid
  clrplane_y(3);
  box_walls(0, 4, 0, 7, 4, 7);
  delay_ms(500);

  //5

  //Pyramid

  clrplane_y(4);
  box_walls(0, 5, 0, 7, 5, 7);
  delay_ms(500);
  //6


  //Pyramid

  clrplane_y(5);
  box_walls(0, 6, 0, 7, 6, 7);
  delay_ms(500);
  //7

  //Pyramid

  clrplane_y(6);
  box_walls(0, 7, 0, 7, 7, 7);
  delay_ms(500);


  clrplane_y(7);
  delay_ms(10000);

}

void zoom_pyramid() {
  int i, j, k, time;

  //1
  fill(0x00);

  box_walls(0, 0, 0, 7, 0, 7);
  delay_ms(500);

  //2
  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 0, 1);

  box_walls(0, 1, 0, 7, 1, 7);
  delay_ms(500);

  //3
  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 1, 1);
  box_wireframe(1, 1, 2, 6, 1, 3);

  box_walls(0, 2, 0, 7, 2, 7);
  delay_ms(500);

  //4
  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 2, 1);
  box_wireframe(1, 1, 2, 6, 2, 3);
  box_wireframe(2, 2, 4, 5, 2, 5);

  box_walls(0, 3, 0, 7, 3, 7);
  delay_ms(500);

  //5
  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 3, 1);
  box_wireframe(1, 1, 2, 6, 3, 3);
  box_wireframe(2, 2, 4, 5, 3, 5);
  box_wireframe(3, 3, 6, 4, 3, 7);

  box_walls(0, 4, 0, 7, 4, 7);
  delay_ms(500);

  //5
  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 4, 1);
  box_wireframe(1, 1, 2, 6, 4, 3);
  box_wireframe(2, 2, 4, 5, 4, 5);
  box_wireframe(3, 3, 6, 4, 4, 7);

  box_walls(0, 5, 0, 7, 5, 7);
  delay_ms(500);
  //6

  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 5, 1);
  box_wireframe(1, 1, 2, 6, 5, 3);
  box_wireframe(2, 2, 4, 5, 5, 5);
  box_wireframe(3, 3, 6, 4, 4, 7);

  box_walls(0, 6, 0, 7, 6, 7);
  delay_ms(500);
  //7
  fill(0x00);
  //Pyramid
  box_wireframe(0, 0, 0, 7, 6, 1);
  box_wireframe(1, 1, 2, 6, 6, 3);
  box_wireframe(2, 2, 4, 5, 5, 5);
  box_wireframe(3, 3, 6, 4, 4, 7);

  box_walls(0, 7, 0, 7, 7, 7);
  delay_ms(500);

  fill(0x00);
  box_wireframe(0, 0, 0, 7, 7, 1);
  box_wireframe(1, 1, 2, 6, 6, 3);
  box_wireframe(2, 2, 4, 5, 5, 5);
  box_wireframe(3, 3, 6, 4, 4, 7);

  delay_ms(10000);
}


void effect_intro() {
  int cnt, cnt_2, time;

  //Bottom To Top

  for (cnt = 0; cnt <= 7; cnt++) {
    box_wireframe(0, 0, 0, 7, 7, cnt);
    delay_ms(2000);
  }
  for (cnt = 0; cnt < 7; cnt++) {
    clrplane_z(cnt);
    delay_ms(2000);
  }

  //Shift Things Right
  //1
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 6);
  }
  delay_ms(2000);
  //2
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 5);
  }
  setvoxel(0, 0, 6);
  setvoxel(7, 0, 6);
  delay_ms(2000);
  //3
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 4);
  }
  setvoxel(0, 0, 5);
  setvoxel(7, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(7, 0, 6);
  delay_ms(2000);

  //4
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 3);
  }
  setvoxel(0, 0, 4);
  setvoxel(7, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(7, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(7, 0, 6);
  delay_ms(2000);

  //5
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 2);
  }
  setvoxel(0, 0, 3);
  setvoxel(7, 0, 3);
  setvoxel(0, 0, 4);
  setvoxel(7, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(7, 0, 5);
  setvoxel(0, 0, 6);
  setvoxel(7, 0, 6);
  delay_ms(2000);

  //6
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 1);
  }
  setvoxel(0, 0, 2);
  setvoxel(7, 0, 2);
  setvoxel(0, 0, 3);
  setvoxel(7, 0, 3);
  setvoxel(0, 0, 4);
  setvoxel(7, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(7, 0, 5);
  delay_ms(2000);


  //7
  shift(AXIS_Y, -1);
  for (cnt = 0; cnt <= 7; cnt++) {
    setvoxel(cnt, 0, 0);
  }
  setvoxel(0, 0, 1);
  setvoxel(7, 0, 1);
  setvoxel(0, 0, 2);
  setvoxel(7, 0, 2);
  setvoxel(0, 0, 3);
  setvoxel(7, 0, 3);
  setvoxel(0, 0, 4);
  setvoxel(7, 0, 4);
  setvoxel(0, 0, 5);
  setvoxel(7, 0, 5);
  delay_ms(2000);

  //Right To Left
  for (cnt = 0; cnt <= 7; cnt++) {
    box_wireframe(0, 0, 0, 7, cnt, 7);
    delay_ms(2000);
  }
  for (cnt = 0; cnt < 7; cnt++) {
    clrplane_y(cnt);
    delay_ms(2000);
  }

  //Shift to the bottom
  for (cnt_2 = 6; cnt_2 >= 0; cnt_2--) {
    shift(AXIS_Z, -1);
    for (cnt = 0; cnt <= 7; cnt++) {
      setvoxel(cnt, cnt_2, 0);
    }
    for (cnt = 6; cnt > cnt_2; cnt--) {
      setvoxel(0, cnt, 0);
      setvoxel(7, cnt, 0);
    }

    delay_ms(2000);
  }

  //Make All Wall Box

  for (cnt = 0; cnt <= 6; cnt++) {
    fill(0x00);
    box_walls(0, 0, 0, 7, 7, cnt);
    delay_ms(2000);
  }

  time = 2000;
  for (cnt_2 = 0; cnt_2 < 5; cnt_2++) {
    time = time - 300;
    //Make Box Smaller
    for (cnt = 0; cnt <= 3; cnt++) {
      fill(0x00);
      box_walls(cnt, cnt, cnt, 7 - cnt, 7 - cnt, 7 - cnt);
      delay_ms(time);
    }

    //Make Box Bigger
    for (cnt = 0; cnt <= 3; cnt++) {
      fill(0x00);
      box_walls(3 - cnt, 3 - cnt, 3 - cnt, 4 + cnt, 4 + cnt, 4 + cnt);
      delay_ms(time);
    }
  }
  for (cnt_2 = 0; cnt_2 < 5; cnt_2++) {
    time = time + 300;
    //Make Box Smaller
    for (cnt = 0; cnt <= 3; cnt++) {
      fill(0x00);
      box_walls(cnt, cnt, cnt, 7 - cnt, 7 - cnt, 7 - cnt);
      delay_ms(time);
    }

    //Make Box Bigger
    for (cnt = 0; cnt <= 3; cnt++) {
      fill(0x00);
      box_walls(3 - cnt, 3 - cnt, 3 - cnt, 4 + cnt, 4 + cnt, 4 + cnt);
      delay_ms(time);
    }
  }
  delay_ms(2000);

}



// ==========================================================================================
//   Draw functions
// ==========================================================================================


// Set a single voxel to ON
void setvoxel(int x, int y, int z)
{
  if (inrange(x,y,z))
    cube[z][y] |= (1 << x);
}


// Set a single voxel to ON
void clrvoxel(int x, int y, int z)
{
  if (inrange(x,y,z))
    cube[z][y] &= ~(1 << x);
}



// This function validates that we are drawing inside the cube.
unsigned char inrange(int x, int y, int z)
{
  if (x >= 0 && x < 8 && y >= 0 && y < 8 && z >= 0 && z < 8)
  {
    return 0x01;
  } else
  {
    // One of the coordinates was outside the cube.
    return 0x00;
  }
}

// Get the current status of a voxel
unsigned char getvoxel(int x, int y, int z)
{
  if (inrange(x,y,z))
  {
    if (cube[z][y] & (1 << x))
    {
      return 0x01;
    } else
    {
      return 0x00;
    }
  } else
  {
    return 0x00;
  }
}

// In some effect we want to just take bool and write it to a voxel
// this function calls the apropriate voxel manipulation function.
void altervoxel(int x, int y, int z, int state)
{
  if (state == 1)
  {
    setvoxel(x,y,z);
  } else
  {
    clrvoxel(x,y,z);
  }
}

// Flip the state of a voxel.
// If the voxel is 1, its turned into a 0, and vice versa.
void flpvoxel(int x, int y, int z)
{
  if (inrange(x, y, z))
    cube[z][y] ^= (1 << x);
}

// Makes sure x1 is alwas smaller than x2
// This is usefull for functions that uses for loops,
// to avoid infinite loops
void argorder(int ix1, int ix2, int *ox1, int *ox2)
{
  if (ix1>ix2)
  {
    int tmp;
    tmp = ix1;
    ix1= ix2;
    ix2 = tmp;
  }
  *ox1 = ix1;
  *ox2 = ix2;
}

// Sets all voxels along a X/Y plane at a given point
// on axis Z
void setplane_z (int z)
{
  int i;
  if (z>=0 && z<8)
  {
    for (i=0;i<8;i++)
      cube[z][i] = 0xff;
  }
}

// Clears voxels in the same manner as above
void clrplane_z (int z)
{
  int i;
  if (z>=0 && z<8)
  {
    for (i=0;i<8;i++)
      cube[z][i] = 0x00;
  }
}

void setplane_x (int x)
{
  int z;
  int y;
  if (x>=0 && x<8){
    for (z=0;z<8;z++){
      for (y=0;y<8;y++){
        cube[z][y] |= (1 << x);
      }
    }
  }
}

void clrplane_x (int x)
{
  int z;
  int y;
  if (x>=0 && x<8){
    for (z=0;z<8;z++){
      for (y=0;y<8;y++){
        cube[z][y] &= ~(1 << x);
      }
    }
  }
}

void setplane_y (int y)
{
  int z;
  if (y>=0 && y<8)
  {
    for (z=0;z<8;z++)
      cube[z][y] = 0xff;
  } 
}

void clrplane_y (int y)
{
  int z;
  if (y>=0 && y<8)
  {
    for (z=0;z<8;z++)
      cube[z][y] = 0x00; 
  }
}

void setplane (char axis, unsigned char i)
{
    switch (axis)
    {
        case AXIS_X:
            setplane_x(i);
            break;
        
       case AXIS_Y:
            setplane_y(i);
            break;

       case AXIS_Z:
            setplane_z(i);
            break;
    }
}

void clrplane (char axis, unsigned char i)
{
    switch (axis)
    {
        case AXIS_X:
            clrplane_x(i);
            break;
        
       case AXIS_Y:
            clrplane_y(i);
            break;

       case AXIS_Z:
            clrplane_z(i);
            break;
    }
}

// Fill a value into all 64 byts of the cube buffer
// Mostly used for clearing. fill(0x00)
// or setting all on. fill(0xff)
void fill (unsigned char pattern)
{
  int z;
  int y;
  for (z=0;z<8;z++)
  {
    for (y=0;y<8;y++)
    {
      cube[z][y] = pattern;
    }
  }
}



// Draw a box with all walls drawn and all voxels inside set
void box_filled(int x1, int y1, int z1, int x2, int y2, int z2)
{
  int iy;
  int iz;

  argorder(x1, x2, &x1, &x2);
  argorder(y1, y2, &y1, &y2);
  argorder(z1, z2, &z1, &z2);

  for (iz=z1;iz<=z2;iz++)
  {
    for (iy=y1;iy<=y2;iy++)
    {
      cube[iz][iy] |= byteline(x1,x2);
    }
  }

}

// Darw a hollow box with side walls.
void box_walls(int x1, int y1, int z1, int x2, int y2, int z2)
{
  int iy;
  int iz;
  
  argorder(x1, x2, &x1, &x2);
  argorder(y1, y2, &y1, &y2);
  argorder(z1, z2, &z1, &z2);

  for (iz=z1;iz<=z2;iz++)
  {
    for (iy=y1;iy<=y2;iy++)
    { 
      if (iy == y1 || iy == y2 || iz == z1 || iz == z2)
      {
        cube[iz][iy] = byteline(x1,x2);
      } else
      {
        cube[iz][iy] |= ((0x01 << x1) | (0x01 << x2));
      }
    }
  }

}

// Draw a wireframe box. This only draws the corners and edges,
// no walls.
void box_wireframe(int x1, int y1, int z1, int x2, int y2, int z2)
{
  int iy;
  int iz;

  argorder(x1, x2, &x1, &x2);
  argorder(y1, y2, &y1, &y2);
  argorder(z1, z2, &z1, &z2);

  // Lines along X axis
  cube[z1][y1] = byteline(x1,x2);
  cube[z1][y2] = byteline(x1,x2);
  cube[z2][y1] = byteline(x1,x2);
  cube[z2][y2] = byteline(x1,x2);

  // Lines along Y axis
  for (iy=y1;iy<=y2;iy++)
  {
    setvoxel(x1,iy,z1);
    setvoxel(x1,iy,z2);
    setvoxel(x2,iy,z1);
    setvoxel(x2,iy,z2);
  }

  // Lines along Z axis
  for (iz=z1;iz<=z2;iz++)
  {
    setvoxel(x1,y1,iz);
    setvoxel(x1,y2,iz);
    setvoxel(x2,y1,iz);
    setvoxel(x2,y2,iz);
  }

}

// Returns a byte with a row of 1s drawn in it.
// byteline(2,5) gives 0b00111100
char byteline (int start, int end)
{
  return ((0xff<<start) & ~(0xff<<(end+1)));
}

// Flips a byte 180 degrees.
// MSB becomes LSB, LSB becomes MSB.
char flipbyte (char byte)
{
  char flop = 0x00;

  flop = (flop & 0b11111110) | (0b00000001 & (byte >> 7));
  flop = (flop & 0b11111101) | (0b00000010 & (byte >> 5));
  flop = (flop & 0b11111011) | (0b00000100 & (byte >> 3));
  flop = (flop & 0b11110111) | (0b00001000 & (byte >> 1));
  flop = (flop & 0b11101111) | (0b00010000 & (byte << 1));
  flop = (flop & 0b11011111) | (0b00100000 & (byte << 3));
  flop = (flop & 0b10111111) | (0b01000000 & (byte << 5));
  flop = (flop & 0b01111111) | (0b10000000 & (byte << 7));
  return flop;
}

// Draw a line between any coordinates in 3d space.
// Uses integer values for input, so dont expect smooth animations.
void line(int x1, int y1, int z1, int x2, int y2, int z2)
{
  float xy; // how many voxels do we move on the y axis for each step on the x axis
  float xz; // how many voxels do we move on the y axis for each step on the x axis 
  unsigned char x,y,z;
  unsigned char lasty,lastz;

  // We always want to draw the line from x=0 to x=7.
  // If x1 is bigget than x2, we need to flip all the values.
  if (x1>x2)
  {
    int tmp;
    tmp = x2; x2 = x1; x1 = tmp;
    tmp = y2; y2 = y1; y1 = tmp;
    tmp = z2; z2 = z1; z1 = tmp;
  }

  
  if (y1>y2)
  {
    xy = (float)(y1-y2)/(float)(x2-x1);
    lasty = y2;
  } else
  {
    xy = (float)(y2-y1)/(float)(x2-x1);
    lasty = y1;
  }

  if (z1>z2)
  {
    xz = (float)(z1-z2)/(float)(x2-x1);
    lastz = z2;
  } else
  {
    xz = (float)(z2-z1)/(float)(x2-x1);
    lastz = z1;
  }



  // For each step of x, y increments by:
  for (x = x1; x<=x2;x++)
  {
    y = (xy*(x-x1))+y1;
    z = (xz*(x-x1))+z1;
    setvoxel(x,y,z);
  }
  
}

// Delay loop.
// This is not calibrated to milliseconds,
// but we had allready made to many effects using this
// calibration when we figured it might be a good idea
// to calibrate it.
void delay_ms(uint16_t x)
{
  uint8_t y, z;
  for ( ; x > 0 ; x--){
    for ( y = 0 ; y < 90 ; y++){
      for ( z = 0 ; z < 6 ; z++){
        asm volatile ("nop");
      }
    }
  }
}



// Shift the entire contents of the cube along an axis
// This is great for effects where you want to draw something
// on one side of the cube and have it flow towards the other
// side. Like rain flowing down the Z axiz.
void shift (char axis, int direction)
{
  int i, x ,y;
  int ii, iii;
  int state;

  for (i = 0; i < 8; i++)
  {
    if (direction == -1)
    {
      ii = i;
    } else
    {
      ii = (7-i);
    } 
  
  
    for (x = 0; x < 8; x++)
    {
      for (y = 0; y < 8; y++)
      {
        if (direction == -1)
        {
          iii = ii+1;
        } else
        {
          iii = ii-1;
        }
        
        if (axis == AXIS_Z)
        {
          state = getvoxel(x,y,iii);
          altervoxel(x,y,ii,state);
        }
        
        if (axis == AXIS_Y)
        {
          state = getvoxel(x,iii,y);
          altervoxel(x,ii,y,state);
        }
        
        if (axis == AXIS_X)
        {
          state = getvoxel(iii,y,x);
          altervoxel(ii,y,x,state);
        }
      }
    }
  }
  
  if (direction == -1)
  {
    i = 7;
  } else
  {
    i = 0;
  } 
  
  for (x = 0; x < 8; x++)
  {
    for (y = 0; y < 8; y++)
    {
      if (axis == AXIS_Z)
        clrvoxel(x,y,i);
        
      if (axis == AXIS_Y)
        clrvoxel(x,i,y);
      
      if (axis == AXIS_X)
        clrvoxel(i,y,x);
    }
  }
}
';

// on(0:7,0:7,0:7);
// delay_ms(5000);
// off(0:7,0,0);
// delay_ms(2000);
// off(0,0:7,0);
// delay_ms(2000);
// off(0,0,0:7);
// delay_ms(2000);


// on(0,1,2);
// on(0,1,3);
// delay_ms(1200);
// off(0,1,2);
// on(7,7,7);


          //all
          // $pieces = ["on([2,4,8],5,3)", "on(1,2,[2,4,8])", "off(1,[2,4,8],3)", "on([2,4,8],7,[3,5])", "on([2,4,8],[3,5],7)", "on(1,[2,4,8],[3,5])", "off([2,4,8],[2,7],[3,5])"];
          //all
        $text = $input["js_raw"];

        $pieces = explode(";", $text);
        $fruit = array_pop($pieces);

        //$pieces = ["on(9,9,9)", "on(2:8,5,3)", "on(1,2,2:8)", "off(1,2:8,3)", "on(1,2:8,3:5)", "off(2:8,3:5,7)", "on(2:8,7,3:5)", "off(2:8,2:7,3:5)"];

        $partA = "";
        $partB = "";
        $partC = "";
        $funcName = "";
        $stringo = "";
        $stack =array();

        for($i=0; $i < sizeof($pieces); $i++) { 
            // echo "|".$pieces[$i]."|<br>";            
            $funcName = substr( $pieces[$i], strrpos($pieces[$i], '(', 0)-1, 1 );
            // echo "||".$funcName."||<br>";

          $org = $pieces[$i];
          $do =strrpos($pieces[$i], ')', 0);
          $a = substr($pieces[$i], strrpos($pieces[$i], '(', 0),  strrpos($pieces[$i], ')', 0) );

          $tmp = str_replace("[", "[", $a, $count);
          // echo $a."\r\n";  

          if (strpos($a, '[') !== FALSE){
              if($count != 1){
                   $tmp = str_replace("],[", "+", $a);
                   $tmp1 = str_replace("],", "+", $tmp);
                   $tmp2 = str_replace(",[", "+", $tmp1);
                   $tmp3 = str_replace("([", "", $tmp2);
                   $tmp4 = str_replace("])", "", $tmp3);
                   $tmp4b = str_replace(")", "", $tmp4);
                   $tmp4c = str_replace("(", "", $tmp4b);
                   $tmp5 = str_replace("[", "", $tmp4c);
                   $newstring = str_replace("]", "", $tmp5);
                      $inside = explode("+", $newstring);
                      
                  //  tu mam polia
                      $partA = explode(",", $inside[0]);
                      $partB = explode(",", $inside[1]);
                      $partC = explode(",", $inside[2]);
                      // var_dump($partA);
                      // var_dump($partB);
                      // var_dump($partC);
                      for($a = 0; $a<sizeof($partA); $a++){
                          for($b = 0; $b<sizeof($partB); $b++){
                            for($c = 0; $c<sizeof($partC); $c++){
                              if($funcName == "n"){ $fname = "setvoxel"; } else { $fname = "clrvoxel"; }
                                //echo $fname."(".$partA[$a].", ".$partB[$b].", ".$partC[$c].");"."<br>";
                            array_push($stack, $fname."(".$partA[$a].", ".$partB[$b].", ".$partC[$c].");" );
                          }
                        }
                      }
              } else {
                  // $count == 1
                  $tmp = str_replace("(", "", $a);
                  $tmp1 = str_replace(")", "", $tmp);
                  $a = $tmp1;
                  
                  $indA = strrpos($a, '[', 0);
                  $indB = strrpos($a, ']', 0);
                  
                  $before = substr( $a, 0, $indA );
                  $elArr = substr( $a, $indA, $indB );
                  $elArr = str_replace("],", "", $elArr);
                  $elArr = str_replace("]", "", $elArr);
                  $elArr = str_replace("[", "", $elArr);
                  $after = substr( $a, $indB, strlen($a) );
                  $after = str_replace("]", "", $after);
                  $t = str_replace(",", "-", $elArr);
                      
                      $work = $before.$t.$after;
                      $items = explode(",", $work);

                      $partA = explode("-", $items[0]);
                      $partB = explode("-", $items[1]);
                      $partC = explode("-", $items[2]);

              for($a = 0; $a<sizeof($partA); $a++){
                for($b = 0; $b<sizeof($partB); $b++){
                  for($c = 0; $c<sizeof($partC); $c++){
                    if($funcName == "n"){ $fname = "setvoxel"; } else { $fname = "clrvoxel"; }
                    //echo $fname."(".$partA[$a].", ".$partB[$b].", ".$partC[$c].");"."<br>";
                    array_push($stack, $fname."(".$partA[$a].", ".$partB[$b].", ".$partC[$c].");" );
                  }
                }
              }
                 
                   
              } //END $count ==1
          } //END [ part  
              else 
          
          if (strpos($a, ':') !== FALSE){
               $tmp = str_replace("(", "", $a);
                  $tmp1 = str_replace(")", "", $tmp);
                  $a = $tmp1;
              
              $items = explode(",", $a);
              $partA = explode(":", $items[0]);
              $partB = explode(":", $items[1]);
              $partC = explode(":", $items[2]);

              for($a = $partA[0], $aa = 0; $aa<=($partA[sizeof($partA)-1])-$partA[0]; $a++, $aa++){
                for($b = $partB[0], $bb = 0; $bb<=($partB[sizeof($partB)-1])-$partB[0]; $b++, $bb++){
                  for($c = $partC[0], $cc = 0; $cc<=($partC[sizeof($partC)-1])-$partC[0]; $c++, $cc++){
                    if($funcName == "n"){ $fname = "setvoxel"; } else { $fname = "clrvoxel"; }
                    // echo $funcName."<br>";
              //      echo $fname."(".$a.", ".$b.", ".$c.");"."\r\n";
                    array_push($stack, $fname."(".$a.", ".$b.", ".$c.");" );
                  }
                }
              }
              
              
          } // END : part 
              else {
                  //klasicke x1 y1 z1
                  if($funcName == "n"){ 
                      // echo str_replace("on","setvoxel", $org).";";
                      array_push($stack, str_replace("on","setvoxel", $org).";" );
                  } else { 
                      // echo str_replace("off","clrvoxel", $org).";";
                      array_push($stack, str_replace("off","clrvoxel", $org).";" );
                  }
              }
        } //END BIG for() array of functions
                // var_dump($stack);
                // exit();

        if(!file_exists("source" . ".c")){
            $file = tmpfile();
        }
//////////////////////////////////
        foreach ($stack as $item) {
              //echo $item."<br>";
              $stringo .= $item.' ';
            }

            $all = $code_init.$stringo.$code_end;
            // echo $stringo;
             // exit();
                   

        $file = fopen("source" . ".c","a+");

        file_put_contents("source" . ".c", $all);
        fclose($file);            
        $outLog = "";


        // $output = "<pre>".shell_exec("/var/www/olm_app_server/public/./testrunfromphp.sh 2>&1")."</pre>";
        $output = "<pre>".shell_exec("/var/www/olm_app_server/public/./testrunfromphp.sh 2>/var/www/olm_app_server/public/outLog.txt")."</pre>";
        $outLog = shell_exec("cat /var/www/olm_app_server/public/outLog.txt");
        echo $outLog;
        echo "Uploading done.";
 
    }

}