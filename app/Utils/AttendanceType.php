<?php

namespace App\Utils;


enum AttendanceType {
    case presence;
    case late;
    case absence;
}
