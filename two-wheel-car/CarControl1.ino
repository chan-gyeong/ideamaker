#include "CarControl1.h"

void Car1::LeftWheel(int front, int back) {
    digitalWrite(10, front);
    digitalWrite(9, back);
}

void Car1::RightWheel(int front, int back) {
    digitalWrite(6, front);
    digitalWrite(5, back);
}


void Car1::goF(void) {
    LeftWheel(HIGH, LOW);
    RightWheel(HIGH, LOW);
}

void Car1::goB(void) {
    LeftWheel(LOW, HIGH);
    RightWheel(LOW, HIGH);
}

void Car1::turnL(void) {
    LeftWheel(LOW, HIGH);
    RightWheel(HIGH, LOW);
}

void Car1::turnR(void) {
    LeftWheel(HIGH, LOW);
    RightWheel(LOW, HIGH);
}

void Car1::Off(void) {
    LeftWheel(LOW, LOW);
    RightWheel(LOW, LOW);
}
