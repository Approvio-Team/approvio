<?php

namespace Approvio\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use AttachmentController;
use Attachment;
use Application;
use User;
use NotFoundException;
use UnauthorizedException;
use UploadException;
use InvalidFileTypeException;

class AttachmentControllerTest extends TestCase
{
    private $attachmentRepository;
    private $applicationRepository;
    private $uploadDir;
    private $attachmentController;
    private $adminUser;
    private $boardMemberUser;

    protected function setUp(): void
    {
        $this->attachmentRepository = $this->createMock('AttachmentRepository');
        $this->applicationRepository = $this->createMock('ApplicationRepository');
        $this->uploadDir = '/tmp/uploads';

        $this->attachmentController = new AttachmentController(
            $this->attachmentRepository,
            $this->applicationRepository,
            $this->uploadDir
        );

        // Test-Benutzer erstellen
        $this->adminUser = $this->createMock(User::class);
        $this->adminUser->method('isAdmin')->willReturn(true);
        $this->adminUser->method('isBoardMember')->willReturn(false);

        $this->boardMemberUser = $this->createMock(User::class);
        $this->boardMemberUser->method('isAdmin')->willReturn(false);
        $this->boardMemberUser->method('isBoardMember')->willReturn(true);
    }

    public function testUploadAttachment(): void
    {
        // PHPUnit kann nicht direkt mit move_uploaded_file arbeiten, daher müssen wir diesen Test überspringen
        // Üblicherweise würde man hier eine Funktion mocken oder eine Test-Abstraktion verwenden
        $this->markTestSkipped('Test erfordert Mocking von move_uploaded_file oder eine Test-Abstraktion');
    }

    public function testGetAttachmentAsAdmin(): void
    {
        $attachmentId = 1;
        $applicationId = 2;

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);

        $application = $this->createMock(Application::class);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $result = $this->attachmentController->getAttachment(
            $attachmentId,
            $this->adminUser
        );

        $this->assertSame($attachment, $result);
    }

    public function testGetAttachmentAsApplicant(): void
    {
        $attachmentId = 1;
        $applicationId = 2;
        $applicantEmail = 'applicant@example.com';

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $result = $this->attachmentController->getAttachment(
            $attachmentId,
            null,
            $applicantEmail
        );

        $this->assertSame($attachment, $result);
    }

    public function testGetAttachmentWithoutPermission(): void
    {
        $this->expectException(UnauthorizedException::class);

        $attachmentId = 1;
        $applicationId = 2;
        $applicantEmail = 'applicant@example.com';
        $wrongEmail = 'wrong@example.com';

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentController->getAttachment(
            $attachmentId,
            null,
            $wrongEmail
        );
    }

    public function testGetNonExistentAttachment(): void
    {
        $this->expectException(NotFoundException::class);

        $attachmentId = 999; // Nicht existierende ID

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn(null);

        $this->attachmentController->getAttachment(
            $attachmentId,
            $this->adminUser
        );
    }

    public function testListAttachmentsByApplicationAsAdmin(): void
    {
        $applicationId = 1;

        $application = $this->createMock(Application::class);

        $attachments = [
            $this->createMock(Attachment::class),
            $this->createMock(Attachment::class)
        ];

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findByApplicationId')
            ->with($applicationId)
            ->willReturn($attachments);

        $result = $this->attachmentController->listAttachmentsByApplication(
            $applicationId,
            $this->adminUser
        );

        $this->assertSame($attachments, $result);
    }

    public function testListAttachmentsByApplicationAsApplicant(): void
    {
        $applicationId = 1;
        $applicantEmail = 'applicant@example.com';

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);

        $attachments = [
            $this->createMock(Attachment::class),
            $this->createMock(Attachment::class)
        ];

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findByApplicationId')
            ->with($applicationId)
            ->willReturn($attachments);

        $result = $this->attachmentController->listAttachmentsByApplication(
            $applicationId,
            null,
            $applicantEmail
        );

        $this->assertSame($attachments, $result);
    }

    public function testListAttachmentsByNonExistentApplication(): void
    {
        $this->expectException(NotFoundException::class);

        $applicationId = 999; // Nicht existierende ID

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn(null);

        $this->attachmentController->listAttachmentsByApplication(
            $applicationId,
            $this->adminUser
        );
    }

    public function testListAttachmentsByApplicationWithoutPermission(): void
    {
        $this->expectException(UnauthorizedException::class);

        $applicationId = 1;
        $applicantEmail = 'applicant@example.com';
        $wrongEmail = 'wrong@example.com';

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentController->listAttachmentsByApplication(
            $applicationId,
            null,
            $wrongEmail
        );
    }

    public function testDeleteAttachmentAsAdmin(): void
    {
        $attachmentId = 1;
        $applicationId = 2;

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);
        $attachment->method('getFilepath')->willReturn('/path/to/file.pdf');

        $application = $this->createMock(Application::class);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('delete')
            ->with($attachmentId);

        // PHPUnit kann nicht direkt mit file_exists und unlink arbeiten
        // In einer echten Implementierung würde man diese Funktionen mocken

        $result = $this->attachmentController->deleteAttachment(
            $attachmentId,
            $this->adminUser
        );

        $this->assertTrue($result);
    }

    public function testDeleteAttachmentAsApplicantWithPendingApplication(): void
    {
        $attachmentId = 1;
        $applicationId = 2;
        $applicantEmail = 'applicant@example.com';

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);
        $attachment->method('getFilepath')->willReturn('/path/to/file.pdf');

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);
        $application->method('getStatus')->willReturn(Application::STATUS_PENDING);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('delete')
            ->with($attachmentId);

        $result = $this->attachmentController->deleteAttachment(
            $attachmentId,
            null,
            $applicantEmail
        );

        $this->assertTrue($result);
    }

    public function testDeleteAttachmentWithoutPermission(): void
    {
        $this->expectException(UnauthorizedException::class);

        $attachmentId = 1;
        $applicationId = 2;
        $applicantEmail = 'applicant@example.com';
        $wrongEmail = 'wrong@example.com';

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);
        $application->method('getStatus')->willReturn(Application::STATUS_PENDING);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentController->deleteAttachment(
            $attachmentId,
            null,
            $wrongEmail
        );
    }

    public function testDeleteAttachmentAsApplicantWithNonPendingApplication(): void
    {
        $this->expectException(UnauthorizedException::class);

        $attachmentId = 1;
        $applicationId = 2;
        $applicantEmail = 'applicant@example.com';

        $attachment = $this->createMock(Attachment::class);
        $attachment->method('getApplicationId')->willReturn($applicationId);

        $application = $this->createMock(Application::class);
        $application->method('getApplicantEmail')->willReturn($applicantEmail);
        $application->method('getStatus')->willReturn(Application::STATUS_APPROVED);

        $this->attachmentRepository
            ->expects($this->once())
            ->method('findById')
            ->with($attachmentId)
            ->willReturn($attachment);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->attachmentController->deleteAttachment(
            $attachmentId,
            null,
            $applicantEmail
        );
    }
}
