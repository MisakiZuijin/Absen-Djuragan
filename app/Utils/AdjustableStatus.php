<?php

namespace App\Utils;

enum AdjustableStatus: int {
    case PENDING = 0;
    case ACCEPTED = 1;
    case REJECTED = 2;

    public static function toArray(): array {
        return array_column(AdjustableStatus::cases(), 'value');
    }
}
