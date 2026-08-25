<?php

namespace App\DTO;

class ScheduleDTO {
    private int $internId;
    private int $officeId;
    private int $shiftId;
    private string $startPeriod;
    private string $endPeriod;
    private string $type;

    public function __construct(
        int $internId,
        int $officeId,
        int $shiftId,
        string $startPeriod,
        string $endPeriod,
        string $type
    ) {
        $this->internId = $internId;
        $this->officeId = $officeId;
        $this->shiftId = $shiftId;
        $this->startPeriod = $startPeriod;
        $this->endPeriod = $endPeriod;
        $this->type = $type;
    }

    public function getInternId(): int {
        return $this->internId;
    }

    public function getOfficeId(): int {
        return $this->officeId;
    }

    public function getShiftId(): int {
        return $this->shiftId;
    }

    public function getStartPeriod(): string {
        return $this->startPeriod;
    }

    public function getEndPeriod(): string {
        return $this->endPeriod;
    }

    public function getType(): string {
        return $this->type;
    }
}
