#ifndef __CARCONTROL__
#define __CARCONTROL__

//L:left, F:front
#define LF 10 //pinMode
#define LB 9
#define RF 6
#define RB 5

#define GO_F 13
#define GO_B 12
#define TURN_L 8
#define TURN_R 7

class Car1 {
private:
    void LeftWheel(int front, int back);
    void RightWheel(int front, int back);
    
public:
    void goF(void);
    void goB(void);
    void turnL(void);
    void turnR(void);
    void Off(void);
};

#endif
