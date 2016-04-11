#include <Arduino.h>

void setup();
void loop();
#line 1 "src/sketch.ino"
void setup() {                
    // initialize the digital pin as an output.
    // Pin 13 has an LED connected on most Arduino boards:
    pinMode(13, OUTPUT);     
}

void loop() {
    digitalWrite(13, HIGH);   // set the LED on
    delay(1000);              // wait for a second
    digitalWrite(13, LOW);    // set the LED off
    delay(1000);              // wait for a second
}
                