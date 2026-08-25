<?php

namespace App\Mappers;

use App\DTO\AttendanceDTO;
use App\Helper\LogConsole;

class AttendanceMapper {
    /**
     * Convert request data to an AttendanceDTO instance.
     */
    public static function fromRequest(array $requestData): AttendanceDTO {
        return new AttendanceDTO(
            $requestData['user_id'],
            $requestData['stage'],
            (bool) $requestData['is_adjustable'], // Explicitly cast to bool
            $requestData['attendance_id'],
            $requestData['adjustable_id'],
            $requestData['description'] ?? null,
            $requestData['latitude'] ?? null,
            $requestData['longitude'] ?? null
        );
    }
}
