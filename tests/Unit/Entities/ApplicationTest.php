<?php

namespace Approvio\Tests\Unit\Entities;

use PHPUnit\Framework\TestCase;
use Application;
use Attachment;
use Vote;
use DateTime;

class ApplicationTest extends TestCase
{
    private $application;

    protected function setUp(): void
    {
        $this->application = new Application(
            'Test Antrag',
            'Dies ist ein Testantrag',
            1000.0,
            'antragsteller@example.com'
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('Test Antrag', $this->application->getTitle());
        $this->assertEquals('Dies ist ein Testantrag', $this->application->getDescription());
        $this->assertEquals(1000.0, $this->application->getRequestedAmount());
        $this->assertEquals('antragsteller@example.com', $this->application->getApplicantEmail());
        $this->assertEquals(Application::STATUS_PENDING, $this->application->getStatus());
        $this->assertInstanceOf(DateTime::class, $this->application->getCreatedAt());
        $this->assertIsArray($this->application->getAttachments());
        $this->assertIsArray($this->application->getVotes());
        $this->assertEmpty($this->application->getAttachments());
        $this->assertEmpty($this->application->getVotes());
    }

    public function testAddAttachment(): void
    {
        $attachment = $this->createMock(Attachment::class);
        $this->application->addAttachment($attachment);

        $this->assertCount(1, $this->application->getAttachments());
        $this->assertSame($attachment, $this->application->getAttachments()[0]);
    }

    public function testAddVote(): void
    {
        $vote = $this->createMock(Vote::class);
        $vote->method('isApproved')->willReturn(true);

        $this->application->addVote($vote);

        $this->assertCount(1, $this->application->getVotes());
        $this->assertSame($vote, $this->application->getVotes()[0]);
    }

    public function testStatusUpdatedToApprovedWhenMajorityApproved(): void
    {
        // Mehr Zustimmungen als Ablehnungen
        $approveVote1 = $this->createMock(Vote::class);
        $approveVote1->method('isApproved')->willReturn(true);

        $approveVote2 = $this->createMock(Vote::class);
        $approveVote2->method('isApproved')->willReturn(true);

        $rejectVote = $this->createMock(Vote::class);
        $rejectVote->method('isApproved')->willReturn(false);

        $this->application->addVote($approveVote1);
        $this->application->addVote($approveVote2);
        $this->application->addVote($rejectVote);

        $this->assertEquals(Application::STATUS_APPROVED, $this->application->getStatus());
    }

    public function testStatusUpdatedToRejectedWhenMajorityRejected(): void
    {
        // Mehr Ablehnungen als Zustimmungen
        $approveVote = $this->createMock(Vote::class);
        $approveVote->method('isApproved')->willReturn(true);

        $rejectVote1 = $this->createMock(Vote::class);
        $rejectVote1->method('isApproved')->willReturn(false);

        $rejectVote2 = $this->createMock(Vote::class);
        $rejectVote2->method('isApproved')->willReturn(false);

        $this->application->addVote($approveVote);
        $this->application->addVote($rejectVote1);
        $this->application->addVote($rejectVote2);

        $this->assertEquals(Application::STATUS_REJECTED, $this->application->getStatus());
    }

    public function testStatusRemainsUnchangedWhenVotesAreEqual(): void
    {
        // Gleiche Anzahl an Zustimmungen und Ablehnungen
        $approveVote = $this->createMock(Vote::class);
        $approveVote->method('isApproved')->willReturn(true);

        $rejectVote = $this->createMock(Vote::class);
        $rejectVote->method('isApproved')->willReturn(false);

        $this->application->addVote($approveVote);
        $this->application->addVote($rejectVote);

        $this->assertEquals(Application::STATUS_PENDING, $this->application->getStatus());
    }
}
