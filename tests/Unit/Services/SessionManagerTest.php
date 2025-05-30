<?php

namespace Approvio\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use SessionManager;
use User;

class SessionManagerTest extends TestCase
{
    private $sessionManager;
    private $config;

    protected function setUp(): void
    {
        // Für Tests mit Session-Funktionen müssen wir einige Vorbereitungen treffen
        // Da wir die PHP-internen Session-Funktionen nicht einfach mocken können,
        // wird dieser Test etwas vereinfacht

        // In einer realen Implementierung würde man eine Test-Abstraktion verwenden
        // oder die Session-Funktionen mocken

        // Für einfache Tests nehmen wir an, dass die Session-Funktionen vorhanden sind
        if (!function_exists('session_status')) {
            $this->markTestSkipped('Session-Funktionen sind für diesen Test erforderlich');
        }

        // Session starten, falls noch nicht geschehen
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->config = [
            'session_name' => 'approvio_test_session',
            'session_lifetime' => 3600
        ];

        $this->sessionManager = new SessionManager($this->config);

        // Session-Daten zurücksetzen
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Session-Daten zurücksetzen
        $_SESSION = [];
    }

    public function testCreateSession(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(123);
        $user->method('getEmail')->willReturn('user@example.com');
        $user->method('getRole')->willReturn('board_member');

        $result = $this->sessionManager->createSession($user);

        $this->assertTrue($result);
        $this->assertEquals(123, $_SESSION['user_id']);
        $this->assertEquals('user@example.com', $_SESSION['user_email']);
        $this->assertEquals('board_member', $_SESSION['user_role']);
        $this->assertArrayHasKey('created_at', $_SESSION);
        $this->assertArrayHasKey('last_activity', $_SESSION);
    }

    public function testDestroySession(): void
    {
        // Zuerst eine Session erstellen
        $_SESSION['user_id'] = 123;
        $_SESSION['user_email'] = 'user@example.com';
        $_SESSION['user_role'] = 'board_member';

        $result = $this->sessionManager->destroySession();

        $this->assertTrue($result);
        $this->assertEmpty($_SESSION);
    }

    public function testGetCurrentUserId(): void
    {
        // Aktive Session simulieren
        $_SESSION['user_id'] = 123;
        $_SESSION['last_activity'] = time();

        $userId = $this->sessionManager->getCurrentUserId();

        $this->assertEquals(123, $userId);
    }

    public function testGetCurrentUserIdWithExpiredSession(): void
    {
        // Abgelaufene Session simulieren
        $_SESSION['user_id'] = 123;
        $_SESSION['last_activity'] = time() - 3601; // Vor mehr als einer Stunde

        $userId = $this->sessionManager->getCurrentUserId();

        $this->assertNull($userId);
        $this->assertEmpty($_SESSION); // Session sollte zerstört worden sein
    }

    public function testGetCurrentUserIdWithoutSession(): void
    {
        $userId = $this->sessionManager->getCurrentUserId();

        $this->assertNull($userId);
    }

    public function testGetSession(): void
    {
        $_SESSION['test_key'] = 'test_value';

        $session = $this->sessionManager->getSession();

        $this->assertEquals($_SESSION, $session);
        $this->assertEquals('test_value', $session['test_key']);
    }
}
