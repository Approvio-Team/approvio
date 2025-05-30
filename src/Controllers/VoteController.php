<?php

class VoteController
{
    private $voteRepository;
    private $applicationRepository;
    private $applicationController;

    public function __construct($voteRepository, $applicationRepository, $applicationController)
    {
        $this->voteRepository = $voteRepository;
        $this->applicationRepository = $applicationRepository;
        $this->applicationController = $applicationController;
    }

    /**
     * Erstellt eine neue Abstimmung zu einem Antrag
     * Prüft, ob der Benutzer abstimmen darf
     */
    public function createVote($user, $applicationId, $isApproved, $comment = null)
    {
        // Prüfen, ob der Benutzer abstimmen darf
        if (!$user || !$user->canVote()) {
            throw new UnauthorizedException('Keine Berechtigung zur Abstimmung.');
        }

        // Prüfen, ob der Antrag existiert
        $application = $this->applicationRepository->findById($applicationId);
        if (!$application) {
            throw new NotFoundException('Antrag nicht gefunden.');
        }

        // Prüfen, ob der Antrag noch im Status "pending" ist
        if ($application->getStatus() !== Application::STATUS_PENDING) {
            throw new InvalidOperationException('Abstimmung nicht möglich, da der Antrag bereits entschieden wurde.');
        }

        // Prüfen, ob der Benutzer bereits abgestimmt hat
        $existingVote = $this->voteRepository->findByUserAndApplication($user->getId(), $applicationId);
        if ($existingVote) {
            throw new DuplicateVoteException('Sie haben bereits über diesen Antrag abgestimmt.');
        }

        // Abstimmung erstellen
        $vote = new Vote($user->getId(), $applicationId, $isApproved, $comment);
        $savedVote = $this->voteRepository->save($vote);

        // Dem Antrag die Abstimmung hinzufügen
        $application->addVote($vote);

        // Status des Antrags aktualisieren
        $this->applicationController->updateApplicationStatus($applicationId);

        return $savedVote;
    }

    /**
     * Listet alle Abstimmungen zu einem Antrag auf
     * Prüft, ob der Benutzer berechtigt ist, die Abstimmungen zu sehen
     */
    public function listVotesByApplication($user, $applicationId)
    {
        // Nur Vorstandsmitglieder und Admins dürfen Abstimmungen sehen
        if (!$user || (!$user->isBoardMember() && !$user->isAdmin())) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen der Abstimmungen.');
        }

        return $this->voteRepository->findByApplicationId($applicationId);
    }

    /**
     * Ermittelt die aktuelle Anzahl der Zustimmungen und Ablehnungen
     * und gibt eine Zusammenfassung zurück
     */
    public function getVoteSummary($user, $applicationId)
    {
        // Berechtigung prüfen
        if (!$user || (!$user->isBoardMember() && !$user->isAdmin())) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen der Abstimmungszusammenfassung.');
        }

        $votes = $this->voteRepository->findByApplicationId($applicationId);

        $summary = [
            'total' => count($votes),
            'approved' => 0,
            'rejected' => 0,
            'comments' => []
        ];

        foreach ($votes as $vote) {
            if ($vote->isApproved()) {
                $summary['approved']++;
            } else {
                $summary['rejected']++;
            }

            if ($vote->getComment()) {
                $summary['comments'][] = [
                    'userId' => $vote->getUserId(),
                    'comment' => $vote->getComment(),
                    'timestamp' => $vote->getCreatedAt(),
                    'isApproved' => $vote->isApproved()
                ];
            }
        }

        return $summary;
    }
}
