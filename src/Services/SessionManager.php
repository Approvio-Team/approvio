<?php

class SessionManager
{
    private $sessionName;
    private $sessionLifetime;

    public function __construct($config)
    {
        $this->sessionName = $config['session_name'] ?? 'approvio_session';
        $this->sessionLifetime = $config['session_lifetime'] ?? 3600; // 1 Stunde

        $this->initSession();
    }

    /**
     * Initialisiert die Sitzung
     */
    private function initSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Session-Parameter setzen
            session_name($this->sessionName);

            // Sichere Cookies verwenden
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params(
                $this->sessionLifetime,
                $cookieParams['path'],
                $cookieParams['domain'],
                true, // Secure
                true  // HttpOnly
            );

            session_start();
        }
    }

    /**
     * Erstellt eine neue Sitzung für einen Benutzer
     */
    public function createSession($user)
    {
        // Alte Sitzungsdaten löschen
        $_SESSION = [];

        // Neue Sitzung erstellen
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();

        // Session-ID erneuern, um Session-Fixation zu verhindern
        session_regenerate_id(true);

        return true;
    }

    /**
     * Zerstört die aktuelle Sitzung
     */
    public function destroySession()
    {
        // Sitzungsdaten löschen
        $_SESSION = [];

        // Cookie löschen
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Session zerstören
        session_destroy();

        return true;
    }

    /**
     * Gibt die ID des aktuell angemeldeten Benutzers zurück
     */
    public function getCurrentUserId()
    {
        if (isset($_SESSION['user_id'])) {
            // Prüfen, ob die Sitzung abgelaufen ist
            if (time() - $_SESSION['last_activity'] > $this->sessionLifetime) {
                $this->destroySession();
                return null;
            }

            // Letzte Aktivität aktualisieren
            $_SESSION['last_activity'] = time();

            return $_SESSION['user_id'];
        }

        return null;
    }

    /**
     * Gibt die aktuelle Sitzung zurück
     */
    public function getSession()
    {
        return $_SESSION;
    }
}
