<?php

namespace Approvio\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use EmailService;

class EmailServiceTest extends TestCase
{
    private $emailService;
    private $config;

    protected function setUp(): void
    {
        $this->config = [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user@example.com',
            'smtp_password' => 'password123',
            'from_email' => 'noreply@approvio.de',
            'from_name' => 'Approvio'
        ];

        $this->emailService = new EmailService($this->config);
    }

    public function testSendEmail(): void
    {
        // Da die eigentliche E-Mail-Versand-Funktionalität in der Implementierung
        // nur als Platzhalter vorhanden ist, testen wir hier nur die Rückgabewerte
        $to = 'recipient@example.com';
        $subject = 'Test Subject';
        $body = 'Test Email Body';

        $result = $this->emailService->sendEmail($to, $subject, $body);

        $this->assertTrue($result);
    }

    public function testSendHtmlEmail(): void
    {
        $to = 'recipient@example.com';
        $subject = 'Test HTML Subject';
        $body = '<html><body><h1>Test</h1><p>This is an HTML email</p></body></html>';
        $isHtml = true;

        $result = $this->emailService->sendEmail($to, $subject, $body, $isHtml);

        $this->assertTrue($result);
    }
}
