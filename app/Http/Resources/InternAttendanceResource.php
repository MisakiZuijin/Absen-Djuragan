<?php

namespace App\Http\Resources;

use App\Helper\LogConsole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternAttendanceResource extends JsonResource
{
    protected mixed $meta;
    public function __construct(mixed $resource, $meta = [])
    {
        parent::__construct($resource);

        $this->meta = $meta;
    }

    public function toArray(Request $request): array
    {
        // Ensure $internAttendance is an array and handle potential null or empty values
        $internAttendance = $this->resource['listAttendance'] ?? [];

        return [
            "status" => true,
            "status_code" => 200,
            "message" => 'success retrieve data',
            'data' => [
                'listAttendance' => $internAttendance,
                'dateNow' => $this->resource['dateNow'] ?? null,
                'dateNowYMD' => $this->resource['dateNowYMD'] ?? null,
                'attendanceTotal' => $this->resource['attendanceTotal'] ?? 0,
                'absenceTotal' => $this->resource['absenceTotal'] ?? 0,
                'permitTotal' => $this->resource['permitTotal'] ?? 0,
            ],
            'meta' => $this->meta,
        ];
    }
}
