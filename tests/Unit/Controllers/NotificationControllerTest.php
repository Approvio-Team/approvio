<?php

namespace Approvio\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use NotificationController;
use User;
use Application;

class NotificationControllerTest extends TestCase
{
    private $userRepository;
    private $emailService;
    private $notificationController;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock('UserRepository');
        $this->emailService = $this->createMock('EmailService');

        $this->notificationController = new NotificationController(
            $this->userRepository,
            $this->emailService
        );
    }

    public function testNotifyBoardAboutNewApplication(): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getTitle')->willReturn('Test Antrag');
        $application->method('getDescription')->willReturn('Test Beschreibung');
        $application->method('getRequestedAmount')->willReturn(1000.0);
        $application->method('getApplicantEmail')->willReturn('applicant@example.com');

        $boardMember1 = $this->createMock(User::class);
        $boardMember1->method('getName')->willReturn('Board Member 1');
        $boardMember1->method('getEmail')->willReturn('board1@example.com');

        $boardMember2 = $this->createMock(User::class);
        $boardMember2->method('getName')->willReturn('Board Member 2');
        $boardMember2->method('getEmail')->willReturn('board2@example.com');

        $boardMembers = [$boardMember1, $boardMember2];

        $this->userRepository
            ->expects($this->once())
            ->method('findBoardMembers')
            ->with(true) // Nur aktive Mitglieder
            ->willReturn($boardMembers);

        $this->emailService
            ->expects($this->exactly(2))
            ->method('sendEmail')
            ->willReturn(true);

        $result = $this->notificationController->notifyBoardAboutNewApplication($application);

        $this->assertEquals(2, $result); // Anzahl der benachrichtigten Vorstandsmitglieder
    }

    public function testNotifyApplicantAboutDecision(): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getTitle')->willReturn('Test Antrag');
        $application->method('getDescription')->willReturn('Test Beschreibung');
        $application->method('getRequestedAmount')->willReturn(1000.0);
        $application->method('getApplicantEmail')->willReturn('applicant@example.com');
        $application->method('getStatus')->willReturn(Application::STATUS_APPROVED);

        $this->emailService
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                'applicant@example.com',
                $this->stringContains('Entscheidung zu Ihrem Förderantrag'),
                $this->stringContains('genehmigt')
            )
            ->willReturn(true);

        $result = $this->notificationController->notifyApplicantAboutDecision($application);

        $this->assertTrue($result);
    }

    public function testNotifyApplicantAboutRejection(): void
    {
        $application = $this->createMock(Application::class);
        $application->method('getTitle')->willReturn('Test Antrag');
        $application->method('getDescription')->willReturn('Test Beschreibung');
        $application->method('getRequestedAmount')->willReturn(1000.0);
        $application->method('getApplicantEmail')->willReturn('applicant@example.com');
        $application->method('getStatus')->willReturn(Application::STATUS_REJECTED);

        $this->emailService
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                'applicant@example.com',
                $this->stringContains('Entscheidung zu Ihrem Förderantrag'),
                $this->stringContains('abgelehnt')
            )
            ->willReturn(true);

        $result = $this->notificationController->notifyApplicantAboutDecision($application);

        $this->assertTrue($result);
    }
}
