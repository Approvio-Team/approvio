<?php

class AuthController
{
    private $userRepository;
    private $sessionManager;

    public function __construct($userRepository, $sessionManager)
    {
        $this->userRepository = $userRepository;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Authentifiziert einen Benutzer und erstellt eine Sitzung
     */
    public function login($email, $password)
    {
        // Benutzer anhand der E-Mail finden
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new InvalidCredentialsException('Ungültige Anmeldedaten.');
        }

        // Prüfen, ob der Benutzer aktiv ist
        if (!$user->isActive()) {
            throw new InactiveUserException('Ihr Konto ist deaktiviert. Bitte kontaktieren Sie den Administrator.');
        }

        // Passwort überprüfen
        if (!$this->verifyPassword($password, $user->getPasswordHash())) {
            throw new InvalidCredentialsException('Ungültige Anmeldedaten.');
        }

        // Sitzung erstellen
        $this->sessionManager->createSession($user);

        return $user;
    }

    /**
     * Beendet die aktuelle Sitzung
     */
    public function logout()
    {
        $this->sessionManager->destroySession();
        return true;
    }

    /**
     * Gibt den aktuell angemeldeten Benutzer zurück
     */
    public function getCurrentUser()
    {
        $userId = $this->sessionManager->getCurrentUserId();
        if (!$userId) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    /**
     * Überprüft, ob ein Benutzer angemeldet ist
     */
    public function isLoggedIn()
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Überprüft, ob der aktuelle Benutzer ein Admin ist
     */
    public function isAdmin()
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->isAdmin();
    }

    /**
     * Überprüft, ob der aktuelle Benutzer ein Vorstandsmitglied ist
     */
    public function isBoardMember()
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->isBoardMember();
    }

    /**
     * Überprüft ein Passwort gegen den Hash
     */
    private function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
