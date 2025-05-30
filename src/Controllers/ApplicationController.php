<?php

class ApplicationController
{
    private $applicationRepository;
    private $notificationController;

    public function __construct($applicationRepository, $notificationController)
    {
        $this->applicationRepository = $applicationRepository;
        $this->notificationController = $notificationController;
    }

    /**
     * Erstellt einen neuen Förderantrag ohne Benutzeranmeldung
     */
    public function createApplication($title, $description, $requestedAmount, $applicantEmail)
    {
        // Validierung der Eingaben
        if (empty($title) || empty($description) || $requestedAmount <= 0 || !filter_var($applicantEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ungültige Antragsdaten. Bitte alle Felder korrekt ausfüllen.');
        }

        // Erstellen des Antrags
        $application = new Application($title, $description, $requestedAmount, $applicantEmail);
        $savedApplication = $this->applicationRepository->save($application);

        // Benachrichtigung an alle Vorstandsmitglieder senden
        $this->notificationController->notifyBoardAboutNewApplication($savedApplication);

        return $savedApplication;
    }

    /**
     * Holt einen Antrag anhand seiner ID
     */
    public function getApplication($applicationId)
    {
        return $this->applicationRepository->findById($applicationId);
    }

    /**
     * Listet alle Anträge auf
     * Prüft, ob der Benutzer berechtigt ist, die Liste zu sehen
     */
    public function listApplications($user, $filter = [])
    {
        // Nur Vorstandsmitglieder und Admins dürfen alle Anträge sehen
        if (!$user || (!$user->isBoardMember() && !$user->isAdmin())) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen der Antragsliste.');
        }

        return $this->applicationRepository->findAll($filter);
    }

    /**
     * Aktualisiert den Status eines Antrags basierend auf den abgegebenen Stimmen
     * Wird normalerweise durch VoteController ausgelöst
     */
    public function updateApplicationStatus($applicationId)
    {
        $application = $this->applicationRepository->findById($applicationId);
        if (!$application) {
            throw new NotFoundException('Antrag nicht gefunden.');
        }

        // Speichern des Antrags mit aktualisiertem Status
        $this->applicationRepository->save($application);

        // Wenn der Status nicht mehr "pending" ist, Antragsteller benachrichtigen
        if ($application->getStatus() !== Application::STATUS_PENDING) {
            $this->notificationController->notifyApplicantAboutDecision($application);
        }

        return $application;
    }

    /**
     * Exportiert Anträge in einem bestimmten Format (CSV, PDF)
     * Nur für Vorstandsmitglieder und Admins
     */
    public function exportApplications($user, $format = 'csv', $filter = [])
    {
        // Berechtigung prüfen
        if (!$user || (!$user->isBoardMember() && !$user->isAdmin())) {
            throw new UnauthorizedException('Keine Berechtigung zum Exportieren der Anträge.');
        }

        // Anträge abrufen
        $applications = $this->applicationRepository->findAll($filter);

        // Export-Logik je nach Format
        switch ($format) {
            case 'csv':
                // CSV-Export-Logik implementieren
                return $this->generateCsvExport($applications);
            case 'pdf':
                // PDF-Export-Logik implementieren
                return $this->generatePdfExport($applications);
            default:
                throw new InvalidArgumentException('Ungültiges Exportformat.');
        }
    }

    private function generateCsvExport($applications)
    {
        // CSV-Generierungslogik
        // Diese würde in einer echten Implementierung ausführlicher sein
        $csvContent = "ID;Titel;Beschreibung;Betrag;Status;Datum\n";

        foreach ($applications as $app) {
            $csvContent .= $app->getId() . ";"
                . $app->getTitle() . ";"
                . $app->getDescription() . ";"
                . $app->getRequestedAmount() . ";"
                . $app->getStatus() . ";"
                . $app->getCreatedAt()->format('Y-m-d') . "\n";
        }

        return $csvContent;
    }

    private function generatePdfExport($applications)
    {
        // PDF-Generierungslogik
        // In einer echten Implementierung würde hier eine PDF-Bibliothek verwendet
        return 'PDF-Daten würden hier generiert';
    }
}
