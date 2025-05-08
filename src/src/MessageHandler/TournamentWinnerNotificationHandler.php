<?php

namespace App\MessageHandler;

use App\Message\TournamentWinnerNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TournamentWinnerNotificationHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(TournamentWinnerNotification $notification)
    {
        $participant = $notification->getParticipant();
        $winner = $notification->getWinner();
        $tournament = $notification->getTournament();

        // Envoyer un e-mail au participant
        $this->sendEmailNotification($participant, $winner, $tournament);
    }

    private function sendEmailNotification($participant, $winner, $tournament)
    {
        $email = (new Email())
            ->from('no-reply@tournaments.com')
            ->to($participant->getEmail())
            ->subject('Résultats du tournoi : ' . $tournament->getTournamentName())
            ->text(sprintf(
                "Bonjour %s,\n\nLe tournoi '%s' est terminé, et le grand gagnant est %s !\n\nMerci d'avoir participé !",
                $participant->getFullName(),
                $tournament->getTournamentName(),
                $winner->getFullName()
            ));

        // Utilisation de MailerInterface pour envoyer l'e-mail
        $this->mailer->send($email);
    }
}