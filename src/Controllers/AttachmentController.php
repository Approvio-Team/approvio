<?php

class AttachmentController
{
    private $attachmentRepository;
    private $applicationRepository;
    private $uploadDir;

    public function __construct($attachmentRepository, $applicationRepository, $uploadDir)
    {
        $this->attachmentRepository = $attachmentRepository;
        $this->applicationRepository = $applicationRepository;
        $this->uploadDir = $uploadDir;
    }

    /**
     * Hochladen eines Anhangs zu einem Antrag
     * Kann von nicht angemeldeten Benutzern aufgerufen werden, wenn der Antrag neu ist
     */
    public function uploadAttachment($applicationId, $file, $applicantEmail = null)
    {
        // Prüfen, ob der Antrag existiert
        $application = $this->applicationRepository->findById($applicationId);
        if (!$application) {
            throw new NotFoundException('Antrag nicht gefunden.');
        }

        // Wenn der Antrag nicht neu ist, muss der Antragsteller seine E-Mail angeben
        if ($application->getStatus() !== Application::STATUS_PENDING &&
            $applicantEmail !== $application->getApplicantEmail()) {
            throw new UnauthorizedException('Keine Berechtigung zum Hochladen von Anhängen für diesen Antrag.');
        }

        // Datei validieren
        $this->validateFile($file);

        // Datei speichern
        $filename = $this->generateUniqueFilename($file['name']);
        $filepath = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new UploadException('Fehler beim Hochladen der Datei.');
        }

        // Attachment-Objekt erstellen und speichern
        $attachment = new Attachment(
            $applicationId,
            $file['name'],
            $filepath,
            $file['type'],
            $file['size']
        );

        if (!$attachment->isValidFileType()) {
            unlink($filepath); // Datei löschen, wenn der Dateityp ungültig ist
            throw new InvalidFileTypeException('Ungültiger Dateityp. Erlaubt sind PDF, JPEG, PNG und GIF.');
        }

        $savedAttachment = $this->attachmentRepository->save($attachment);

        // Dem Antrag das Attachment hinzufügen
        $application->addAttachment($attachment);
        $this->applicationRepository->save($application);

        return $savedAttachment;
    }

    /**
     * Holt einen Anhang anhand seiner ID
     * Prüft, ob der Benutzer berechtigt ist, den Anhang zu sehen
     */
    public function getAttachment($attachmentId, $user = null, $applicantEmail = null)
    {
        $attachment = $this->attachmentRepository->findById($attachmentId);
        if (!$attachment) {
            throw new NotFoundException('Anhang nicht gefunden.');
        }

        // Holen des zugehörigen Antrags
        $application = $this->applicationRepository->findById($attachment->getApplicationId());

        // Prüfen, ob der Benutzer berechtigt ist, den Anhang zu sehen
        $isAuthorized = false;

        // Vorstandsmitglieder und Admins dürfen alle Anhänge sehen
        if ($user && ($user->isBoardMember() || $user->isAdmin())) {
            $isAuthorized = true;
        }
        // Der Antragsteller darf seine eigenen Anhänge sehen
        elseif ($applicantEmail && $applicantEmail === $application->getApplicantEmail()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen dieses Anhangs.');
        }

        return $attachment;
    }

    /**
     * Listet alle Anhänge zu einem Antrag auf
     * Prüft, ob der Benutzer berechtigt ist, die Anhänge zu sehen
     */
    public function listAttachmentsByApplication($applicationId, $user = null, $applicantEmail = null)
    {
        // Holen des Antrags
        $application = $this->applicationRepository->findById($applicationId);
        if (!$application) {
            throw new NotFoundException('Antrag nicht gefunden.');
        }

        // Prüfen, ob der Benutzer berechtigt ist, die Anhänge zu sehen
        $isAuthorized = false;

        // Vorstandsmitglieder und Admins dürfen alle Anhänge sehen
        if ($user && ($user->isBoardMember() || $user->isAdmin())) {
            $isAuthorized = true;
        }
        // Der Antragsteller darf seine eigenen Anhänge sehen
        elseif ($applicantEmail && $applicantEmail === $application->getApplicantEmail()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen der Anhänge.');
        }

        return $this->attachmentRepository->findByApplicationId($applicationId);
    }

    /**
     * Löscht einen Anhang
     * Nur Vorstandsmitglieder, Admins oder der Antragsteller selbst dürfen Anhänge löschen
     */
    public function deleteAttachment($attachmentId, $user = null, $applicantEmail = null)
    {
        $attachment = $this->attachmentRepository->findById($attachmentId);
        if (!$attachment) {
            throw new NotFoundException('Anhang nicht gefunden.');
        }

        // Holen des zugehörigen Antrags
        $application = $this->applicationRepository->findById($attachment->getApplicationId());

        // Prüfen, ob der Benutzer berechtigt ist, den Anhang zu löschen
        $isAuthorized = false;

        // Vorstandsmitglieder und Admins dürfen alle Anhänge löschen
        if ($user && ($user->isAdmin())) { // Nur Admins dürfen löschen
            $isAuthorized = true;
        }
        // Der Antragsteller darf seine eigenen Anhänge löschen, solange der Antrag noch "pending" ist
        elseif ($applicantEmail &&
                $applicantEmail === $application->getApplicantEmail() &&
                $application->getStatus() === Application::STATUS_PENDING)
        {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            throw new UnauthorizedException('Keine Berechtigung zum Löschen dieses Anhangs.');
        }

        // Physische Datei löschen
        if (file_exists($attachment->getFilepath())) {
            unlink($attachment->getFilepath());
        }

        // Anhang aus der Datenbank löschen
        $this->attachmentRepository->delete($attachmentId);

        return true;
    }

    private function validateFile($file)
    {
        // Prüfen, ob eine Datei hochgeladen wurde
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new UploadException('Fehler beim Hochladen der Datei. Fehlercode: ' . $file['error']);
        }

        // Maximale Dateigröße prüfen (10 MB)
        $maxFileSize = 10 * 1024 * 1024; // 10 MB in Bytes
        if ($file['size'] > $maxFileSize) {
            throw new FileSizeTooLargeException('Die Datei ist zu groß. Maximale Größe: 10 MB.');
        }

        // Dateityp wird später über die isValidFileType-Methode des Attachment-Objekts geprüft
    }

    private function generateUniqueFilename($originalFilename)
    {
        $pathInfo = pathinfo($originalFilename);
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));

        return $timestamp . '_' . $randomString . $extension;
    }
}
