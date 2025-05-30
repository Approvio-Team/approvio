<?php

namespace Approvio\Tests\Unit\Entities;

use PHPUnit\Framework\TestCase;
use Attachment;
use DateTime;

class AttachmentTest extends TestCase
{
    public function testConstructor(): void
    {
        $attachment = new Attachment(
            1, // applicationId
            'test.pdf',
            '/path/to/test.pdf',
            'application/pdf',
            1024 // 1 KB
        );

        $this->assertEquals(1, $attachment->getApplicationId());
        $this->assertEquals('test.pdf', $attachment->getFilename());
        $this->assertEquals('/path/to/test.pdf', $attachment->getFilepath());
        $this->assertEquals('application/pdf', $attachment->getMimeType());
        $this->assertEquals(1024, $attachment->getFilesize());
        $this->assertInstanceOf(DateTime::class, $attachment->getUploadedAt());
    }

    public function testIsValidFileTypeWithValidTypes(): void
    {
        $validTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];

        foreach ($validTypes as $type) {
            $attachment = new Attachment(1, 'test.file', '/path/to/test.file', $type, 1024);
            $this->assertTrue($attachment->isValidFileType(), "Dateityp {$type} sollte gültig sein");
        }
    }

    public function testIsValidFileTypeWithInvalidTypes(): void
    {
        $invalidTypes = [
            'text/plain',
            'application/javascript',
            'application/x-php',
            'application/octet-stream'
        ];

        foreach ($invalidTypes as $type) {
            $attachment = new Attachment(1, 'test.file', '/path/to/test.file', $type, 1024);
            $this->assertFalse($attachment->isValidFileType(), "Dateityp {$type} sollte ungültig sein");
        }
    }
}
