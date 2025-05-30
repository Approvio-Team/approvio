<?php

class UserController
{
    private $userRepository;

    public function __construct($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Erstellt einen neuen Benutzer
     * Nur Admins dürfen Benutzer erstellen
     */
    public function createUser($currentUser, $email, $name, $role = User::ROLE_BOARD_MEMBER)
    {
        // Prüfen, ob der aktuelle Benutzer ein Admin ist
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new UnauthorizedException('Keine Berechtigung zum Erstellen von Benutzern.');
        }

        // Prüfen, ob die E-Mail-Adresse bereits verwendet wird
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new DuplicateUserException('Diese E-Mail-Adresse wird bereits verwendet.');
        }

        // Benutzer erstellen
        $user = new User($email, $name, $role);

        return $this->userRepository->save($user);
    }

    /**
     * Aktualisiert einen bestehenden Benutzer
     * Nur Admins dürfen Benutzer aktualisieren
     */
    public function updateUser($currentUser, $userId, $data)
    {
        // Prüfen, ob der aktuelle Benutzer ein Admin ist
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new UnauthorizedException('Keine Berechtigung zum Aktualisieren von Benutzern.');
        }

        // Benutzer finden
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('Benutzer nicht gefunden.');
        }

        // Benutzer aktualisieren
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        if (isset($data['isActive'])) {
            $user->setActive($data['isActive']);
        }

        return $this->userRepository->save($user);
    }

    /**
     * Holt einen Benutzer anhand seiner ID
     * Nur Admins dürfen Benutzer sehen
     */
    public function getUser($currentUser, $userId)
    {
        // Prüfen, ob der aktuelle Benutzer ein Admin ist
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen von Benutzern.');
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('Benutzer nicht gefunden.');
        }

        return $user;
    }

    /**
     * Listet alle Benutzer auf
     * Nur Admins dürfen alle Benutzer sehen
     */
    public function listUsers($currentUser)
    {
        // Prüfen, ob der aktuelle Benutzer ein Admin ist
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new UnauthorizedException('Keine Berechtigung zum Anzeigen aller Benutzer.');
        }

        return $this->userRepository->findAll();
    }

    /**
     * Deaktiviert einen Benutzer
     * Nur Admins dürfen Benutzer deaktivieren
     */
    public function deactivateUser($currentUser, $userId)
    {
        // Prüfen, ob der aktuelle Benutzer ein Admin ist
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new UnauthorizedException('Keine Berechtigung zum Deaktivieren von Benutzern.');
        }

        // Benutzer finden
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('Benutzer nicht gefunden.');
        }

        // Benutzer deaktivieren
        $user->setActive(false);
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Aktiviert einen Benutzer
     * Nur Admins dürfen Benutzer aktivieren
     */
    public function activateUser($currentUser, $userId)
    {
        // Prüfen, ob der aktuelle Benutzer ein Admin ist
        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new UnauthorizedException('Keine Berechtigung zum Aktivieren von Benutzern.');
        }

        // Benutzer finden
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('Benutzer nicht gefunden.');
        }

        // Benutzer aktivieren
        $user->setActive(true);
        $this->userRepository->save($user);

        return $user;
    }
}
