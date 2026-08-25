<?php

namespace App\DTO;

use App\Helper\LogConsole;

class AttendanceDTO {
    private int $userId;
    private int $stage;
    private bool $isAdjustable; // Matching the naming convention
    private ?string $description; // Adjusted to match 'description' from request
    private ?float $latitude;
    private ?float $longitude;
    private ?int $attendanceId; // Add attendanceId
    private ?int $adjustableId; // Add adjustableId
    private ?int $scheduleId;
    private ?int $detailScheduleId;
    private int $totalChangeTime;

    private ?string $timeNow;

    public function __construct(
        int $userId,
        int $stage,
        bool $isAdjustable,
        ?int $attendanceId = null,
        ?int $adjustableId = null,
        ?string $description = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $scheduleId = null,
        ?int $detailScheduleId = null,
        int $totalChangeTime = 0,
        string $timeNow = null
    ) {
        $this->userId = $userId;
        $this->stage = $stage;
        $this->isAdjustable = $isAdjustable;
        $this->attendanceId = $attendanceId;
        $this->adjustableId = $adjustableId;
        $this->description = $description;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->scheduleId = $scheduleId;
        $this->detailScheduleId = $detailScheduleId;
        $this->totalChangeTime = $totalChangeTime;
        $this->timeNow = $timeNow;
    }

    // Getter for userId
    public function getUserId(): int {
        return $this->userId;
    }

    // Getter for stage
    public function getStage(): int {
        return $this->stage;
    }

    // Getter for isAdjustable
    public function getIsAdjustable(): bool {
        return $this->isAdjustable;
    }

    // Getter for description
    public function getDescription(): ?string {
        return $this->description;
    }

    // Getter for latitude
    public function getLatitude(): ?float {
        return $this->latitude;
    }

    // Getter for longitude
    public function getLongitude(): ?float {
        return $this->longitude;
    }

    // Getter for attendanceId
    public function getAttendanceId(): ?int {
        return $this->attendanceId;
    }

    // Getter for adjustableId
    public function getAdjustableId(): ?int {
        return $this->adjustableId;
    }

    public function getScheduleId(): ?int {
        return $this->scheduleId;
    }

    public function getDetailSchedule(): ?int {
        return $this->detailScheduleId;
    }

    public function getTotalChangeTime(): int {
        return $this->totalChangeTime;
    }

    public function setTimeNow(string $timeNow): void {
        $this->timeNow = $timeNow;
    }

    public function getTimeNow(): string {
        return $this->timeNow;
    }

    // Di AttendanceDTO
    public function setAdjustableId(?int $adjustableId): void {
        $this->adjustableId = $adjustableId;
    }

    public function setScheduleId(?int $scheduleId): void {
        $this->scheduleId = $scheduleId;
    }

    public function setDetailScheduleId(?int $detailScheduleId): void {
        $this->detailScheduleId = $detailScheduleId;
    }

    public function setAttendanceId(?int $attendanceId): void {
    $this->attendanceId = $attendanceId;
}

    public function toArray(): array {
        return [
            'userId' => $this->userId,
            'stage' => $this->stage,
            'isAdjustable' => $this->isAdjustable,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'attendanceId' => $this->attendanceId,
            'adjustableId' => $this->adjustableId,
            'scheduleId' => $this->scheduleId,
            'detailScheduleId' => $this->detailScheduleId,
            'totalChangeTime' => $this->totalChangeTime,
        ];
    }

}
