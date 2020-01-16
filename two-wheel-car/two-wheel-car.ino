#define SEC *1000
#include "CarControl1.h"

void setup() {
    pinMode(LF, OUTPUT);
    pinMode(LB, OUTPUT);
    pinMode(RF, OUTPUT);
    pinMode(RB, OUTPUT);

    pinMode(GO_F, INPUT_PULLUP);
    pinMode(GO_B, INPUT_PULLUP);
    pinMode(TURN_L, INPUT);
    pinMode(TURN_R, INPUT);
}

void loop() {
    int Button_F=digitalRead(13);
    int Button_B=digitalRead(12);
    int Button_L=digitalRead(8);
    int Button_R=digitalRead(7);
    Car1 kang;

    if (Button_F==HIGH) {
        kang.goF();
    } else if (Button_B==HIGH) {
        kang.goB();
    } else if(Button_L==HIGH){
        kang.turnL();
    } else if (Button_R==HIGH) {
        kang.turnR();
    }else{
        kang.Off();
    }
}
