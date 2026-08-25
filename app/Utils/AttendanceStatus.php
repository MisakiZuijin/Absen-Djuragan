<?php

namespace App\Utils;

enum AttendanceStatus: int {
    case AttendanceAndAdjustableTime = 1;
    case ShowBreakPermitChangeTime = 2;
    case ShowBreakPermit = 17;
    case ShowPermitChangeTime = 15;
    case ShowBreakChangeTime = 16;

    case AttendanceIn = 3;
    case AttendanceOut = 4;
    case AdjustableIn = 5;
    case AdjustableOut = 6;
    case StartBreak = 7;
    case EndBreak = 8;
    case StartPermit = 9;
    case EndPermit = 10;
    case StartBreakAdjustable = 11;
    case EndBreakAdjustable = 12;
    case BreakOrBack = 13;
    case AllDone = 14;
}
