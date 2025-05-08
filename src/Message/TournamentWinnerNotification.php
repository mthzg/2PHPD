<?php

namespace App\Message;

use App\Entity\User;
use App\Entity\Tournament;

class TournamentWinnerNotification
{
    private User $participant;
    private User $winner;
    private Tournament $tournament;

    public function __construct(User $participant, User $winner, Tournament $tournament)
    {
        $this->participant = $participant;
        $this->winner = $winner;
        $this->tournament = $tournament;
    }

    public function getParticipant(): User
    {
        return $this->participant;
    }

    public function getWinner(): User
    {
        return $this->winner;
    }

    public function getTournament(): Tournament
    {
        return $this->tournament;
    }
}