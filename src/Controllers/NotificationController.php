<?php

class NotificationController
{
    private $userRepository;
    private $emailService;

    public function __construct($userRepository, $emailService)
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    /**
     * Benachrichtigt alle Vorstandsmitglieder über einen neuen Antrag
     */
    public function notifyBoardAboutNewApplication($application)
    {
        // Alle aktiven Vorstandsmitglieder finden
        $boardMembers = $this->userRepository->findBoardMembers(true); // true = nur aktive

        foreach ($boardMembers as $user) {
            $this->sendNewApplicationEmail($user, $application);
        }

        return count($boardMembers);
    }

    /**
     * Benachrichtigt den Antragsteller über die Entscheidung zu seinem Antrag
     */
    public function notifyApplicantAboutDecision($application)
    {
        $this->sendApplicationDecisionEmail($application);
        return true;
    }

    /**
     * Sendet eine E-Mail an ein Vorstandsmitglied über einen neuen Antrag
     */
    private function sendNewApplicationEmail($user, $application)
    {
        $subject = 'Neuer Förderantrag eingereicht: ' . $application->getTitle();

        $body = "Sehr geehrte/r {$user->getName()},\n\n"
             . "ein neuer Förderantrag wurde eingereicht und erfordert Ihre Prüfung:\n\n"
             . "Titel: {$application->getTitle()}\n"
             . "Beschreibung: {$application->getDescription()}\n"
             . "Beantragter Betrag: {$application->getRequestedAmount()} €\n"
             . "Eingereicht von: {$application->getApplicantEmail()}\n\n"
             . "Bitte loggen Sie sich ein, um den Antrag zu prüfen und Ihre Stimme abzugeben.\n\n"
             . "Mit freundlichen Grüßen,\nIhr Approvio-System";

        return $this->emailService->sendEmail($user->getEmail(), $subject, $body);
    }

    /**
     * Sendet eine E-Mail an den Antragsteller über die Entscheidung
     */
    private function sendApplicationDecisionEmail($application)
    {
        $subject = 'Entscheidung zu Ihrem Förderantrag: ' . $application->getTitle();

        $statusText = $application->getStatus() === Application::STATUS_APPROVED
            ? 'genehmigt'
            : 'abgelehnt';

        $body = "Sehr geehrte/r Antragsteller/in,\n\n"
             . "wir möchten Sie darüber informieren, dass Ihr Förderantrag {$statusText} wurde:\n\n"
             . "Titel: {$application->getTitle()}\n"
             . "Beschreibung: {$application->getDescription()}\n"
             . "Beantragter Betrag: {$application->getRequestedAmount()} €\n\n";

        if ($application->getStatus() === Application::STATUS_APPROVED) {
            $body .= "Der Vorstand hat Ihrem Antrag zugestimmt. Sie werden in Kürze weitere Informationen "
                  . "zur Auszahlung erhalten.\n\n";
        } else {
            $body .= "Der Vorstand konnte Ihrem Antrag leider nicht zustimmen. Bei Fragen "
                  . "kontaktieren Sie uns gerne.\n\n";
        }

        $body .= "Mit freundlichen Grüßen,\nDer Vorstand";

        return $this->emailService->sendEmail($application->getApplicantEmail(), $subject, $body);
    }
}
