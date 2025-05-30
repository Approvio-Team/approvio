<?php

class EmailService
{
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    private $fromName;

    public function __construct($config)
    {
        $this->smtpHost = $config['smtp_host'];
        $this->smtpPort = $config['smtp_port'];
        $this->smtpUsername = $config['smtp_username'];
        $this->smtpPassword = $config['smtp_password'];
        $this->fromEmail = $config['from_email'];
        $this->fromName = $config['from_name'];
    }

    /**
     * Sendet eine E-Mail
     */
    public function sendEmail($to, $subject, $body, $isHtml = false)
    {
        // Diese Implementierung ist ein Platzhalter
        // In einer echten Anwendung würde hier eine SMTP-Bibliothek wie PHPMailer verwendet

        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if ($isHtml) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }

        // In einer echten Implementierung würde hier die E-Mail über SMTP versendet
        // Für Testzwecke geben wir einen Erfolg zurück
        // mail($to, $subject, $body, $headers);

        // Loggen der E-Mail für Debugging-Zwecke
        $this->logEmail($to, $subject, $body);

        return true;
    }

    /**
     * Loggt eine E-Mail für Debugging-Zwecke
     */
    private function logEmail($to, $subject, $body)
    {
        $logMessage = date('Y-m-d H:i:s') . " - E-Mail an: {$to}, Betreff: {$subject}\n";
        // In einer echten Implementierung würde hier in eine Logdatei geschrieben
        // file_put_contents('emails.log', $logMessage, FILE_APPEND);
    }
}
