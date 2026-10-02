<?php

namespace App\Helper;

class ActionResult
{

    private bool $isSuccess;
    private string $message;
    private mixed $data;

    public function __construct(bool $isSuccess, string $message, mixed $data = null)
    {
        $this->isSuccess = $isSuccess;
        $this->message = $message;
        $this->data = $data;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            "status" => $this->isSuccess,
            "message" => $this->message,
            "data" => $this->data
        ];
    }
}
