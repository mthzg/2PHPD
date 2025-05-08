<?php

namespace App\Message;

class MatchUpdateNotification
{
    private string $matchId;
    private string $updateMessage;

    public function __construct(string $matchId, string $updateMessage)
    {
        $this->matchId = $matchId;
        $this->updateMessage = $updateMessage;
    }

    public function getMatchId(): string
    {
        return $this->matchId;
    }

    public function getUpdateMessage(): string
    {
        return $this->updateMessage;
    }
}