<?php

namespace Approvio\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use ApplicationController;
use Application;
use User;
use InvalidArgumentException;
use UnauthorizedException;
use NotFoundException;

class ApplicationControllerTest extends TestCase
{
    private $applicationRepository;
    private $notificationController;
    private $applicationController;
    private $adminUser;
    private $boardMemberUser;
    private $regularUser;

    protected function setUp(): void
    {
        $this->applicationRepository = $this->createMock('ApplicationRepository');
        $this->notificationController = $this->createMock('NotificationController');

        $this->applicationController = new ApplicationController(
            $this->applicationRepository,
            $this->notificationController
        );

        // Test-Benutzer erstellen
        $this->adminUser = $this->createMock(User::class);
        $this->adminUser->method('isAdmin')->willReturn(true);
        $this->adminUser->method('isBoardMember')->willReturn(false);

        $this->boardMemberUser = $this->createMock(User::class);
        $this->boardMemberUser->method('isAdmin')->willReturn(false);
        $this->boardMemberUser->method('isBoardMember')->willReturn(true);

        $this->regularUser = $this->createMock(User::class);
        $this->regularUser->method('isAdmin')->willReturn(false);
        $this->regularUser->method('isBoardMember')->willReturn(false);
    }

    public function testCreateApplicationWithValidData(): void
    {
        $title = 'Test Antrag';
        $description = 'Dies ist ein Testantrag';
        $requestedAmount = 1000.0;
        $applicantEmail = 'antragsteller@example.com';

        $application = $this->createMock(Application::class);

        $this->applicationRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($application);

        $this->notificationController
            ->expects($this->once())
            ->method('notifyBoardAboutNewApplication')
            ->with($application);

        $result = $this->applicationController->createApplication(
            $title,
            $description,
            $requestedAmount,
            $applicantEmail
        );

        $this->assertSame($application, $result);
    }

    public function testCreateApplicationWithInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Leerer Titel sollte einen Fehler auslösen
        $this->applicationController->createApplication(
            '',
            'Beschreibung',
            1000.0,
            'email@example.com'
        );
    }

    public function testCreateApplicationWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->applicationController->createApplication(
            'Titel',
            'Beschreibung',
            1000.0,
            'keine-email'
        );
    }

    public function testCreateApplicationWithInvalidAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->applicationController->createApplication(
            'Titel',
            'Beschreibung',
            -100.0, // Negativer Betrag
            'email@example.com'
        );
    }

    public function testGetApplication(): void
    {
        $applicationId = 123;
        $application = $this->createMock(Application::class);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $result = $this->applicationController->getApplication($applicationId);

        $this->assertSame($application, $result);
    }

    public function testListApplicationsAsAdmin(): void
    {
        $applications = [
            $this->createMock(Application::class),
            $this->createMock(Application::class)
        ];

        $this->applicationRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($applications);

        $result = $this->applicationController->listApplications($this->adminUser);

        $this->assertSame($applications, $result);
    }

    public function testListApplicationsAsBoardMember(): void
    {
        $applications = [
            $this->createMock(Application::class),
            $this->createMock(Application::class)
        ];

        $this->applicationRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($applications);

        $result = $this->applicationController->listApplications($this->boardMemberUser);

        $this->assertSame($applications, $result);
    }

    public function testListApplicationsAsRegularUser(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->applicationController->listApplications($this->regularUser);
    }

    public function testUpdateApplicationStatus(): void
    {
        $applicationId = 123;
        $application = $this->createMock(Application::class);
        $application->method('getStatus')->willReturn(Application::STATUS_APPROVED);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->applicationRepository
            ->expects($this->once())
            ->method('save')
            ->with($application)
            ->willReturn($application);

        $this->notificationController
            ->expects($this->once())
            ->method('notifyApplicantAboutDecision')
            ->with($application);

        $result = $this->applicationController->updateApplicationStatus($applicationId);

        $this->assertSame($application, $result);
    }

    public function testUpdateApplicationStatusWithNonExistentApplication(): void
    {
        $this->expectException(NotFoundException::class);

        $applicationId = 999; // Nicht existierende ID

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn(null);

        $this->applicationController->updateApplicationStatus($applicationId);
    }

    public function testUpdateApplicationStatusWithPendingStatus(): void
    {
        $applicationId = 123;
        $application = $this->createMock(Application::class);
        $application->method('getStatus')->willReturn(Application::STATUS_PENDING);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->applicationRepository
            ->expects($this->once())
            ->method('save')
            ->with($application)
            ->willReturn($application);

        $this->notificationController
            ->expects($this->never())
            ->method('notifyApplicantAboutDecision');

        $result = $this->applicationController->updateApplicationStatus($applicationId);

        $this->assertSame($application, $result);
    }

    public function testExportApplicationsAsCSV(): void
    {
        $applications = [
            $this->createApplicationMock(1, 'Antrag 1', 'Beschreibung 1', 1000.0, Application::STATUS_PENDING),
            $this->createApplicationMock(2, 'Antrag 2', 'Beschreibung 2', 2000.0, Application::STATUS_APPROVED)
        ];

        $this->applicationRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($applications);

        $result = $this->applicationController->exportApplications($this->adminUser, 'csv');

        $this->assertIsString($result);
        $this->assertStringContainsString('ID;Titel;Beschreibung;Betrag;Status;Datum', $result);
        $this->assertStringContainsString('1;Antrag 1;Beschreibung 1;1000;pending', $result);
        $this->assertStringContainsString('2;Antrag 2;Beschreibung 2;2000;approved', $result);
    }

    public function testExportApplicationsAsPDF(): void
    {
        $applications = [
            $this->createMock(Application::class),
            $this->createMock(Application::class)
        ];

        $this->applicationRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($applications);

        $result = $this->applicationController->exportApplications($this->adminUser, 'pdf');

        $this->assertEquals('PDF-Daten würden hier generiert', $result);
    }

    public function testExportApplicationsWithInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->applicationController->exportApplications($this->adminUser, 'invalid_format');
    }

    public function testExportApplicationsWithoutPermission(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->applicationController->exportApplications($this->regularUser, 'csv');
    }

    /**
     * Hilfsmethode zum Erstellen eines Application-Mocks mit spezifischen Rückgabewerten
     */
    private function createApplicationMock(int $id, string $title, string $description, float $amount, string $status): Application
    {
        $createdAt = new \DateTime();

        $application = $this->createMock(Application::class);
        $application->method('getId')->willReturn($id);
        $application->method('getTitle')->willReturn($title);
        $application->method('getDescription')->willReturn($description);
        $application->method('getRequestedAmount')->willReturn($amount);
        $application->method('getStatus')->willReturn($status);
        $application->method('getCreatedAt')->willReturn($createdAt);

        return $application;
    }
}
