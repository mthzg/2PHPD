<?php

namespace App\Message;

class AdminNotification
{
    private string $message;
    private array $details;

    public function __construct(string $message, array $details = [])
    {
        $this->message = $message;
        $this->details = $details;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}