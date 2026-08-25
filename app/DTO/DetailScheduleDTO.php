<?php

namespace App\DTO;

class DetailScheduleDTO {
    private int $scheduleId;
    private int $shiftId;
    private int $officeId;
    private string $date;
    private string $type;
    private string $workType;
    private bool $isChangeSchedule;
    private bool $isBackFirst;

    public function __construct(
        int $scheduleId,
        int $shiftId,
        int $officeId,
        string $date,
        string $type,
        string $workType,
        bool $isChangeSchedule,
        bool $isBackFirst
    ) {
        $this->scheduleId = $scheduleId;
        $this->shiftId = $shiftId;
        $this->officeId = $officeId;
        $this->date = $date;
        $this->type = $type;
        $this->workType = $workType;
        $this->isChangeSchedule = $isChangeSchedule;
        $this->isBackFirst = $isBackFirst;
    }

    public function getScheduleId(): int {
        return $this->scheduleId;
    }

    public function getShiftId(): int {
        return $this->shiftId;
    }

    public function getOfficeId(): int {
        return $this->officeId;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getWorkType(): string {
        return $this->workType;
    }

    public function isChangeSchedule(): bool {
        return $this->isChangeSchedule;
    }

    public function isBackFirst(): bool {
        return $this->isBackFirst;
    }
}
