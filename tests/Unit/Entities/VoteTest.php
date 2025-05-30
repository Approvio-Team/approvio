<?php

namespace Approvio\Tests\Unit\Entities;

use PHPUnit\Framework\TestCase;
use Vote;
use DateTime;

class VoteTest extends TestCase
{
    public function testConstructorWithoutComment(): void
    {
        $vote = new Vote(1, 2, true);

        $this->assertEquals(1, $vote->getUserId());
        $this->assertEquals(2, $vote->getApplicationId());
        $this->assertTrue($vote->isApproved());
        $this->assertNull($vote->getComment());
        $this->assertInstanceOf(DateTime::class, $vote->getCreatedAt());
    }

    public function testConstructorWithComment(): void
    {
        $vote = new Vote(1, 2, false, 'Dies ist ein Kommentar');

        $this->assertEquals(1, $vote->getUserId());
        $this->assertEquals(2, $vote->getApplicationId());
        $this->assertFalse($vote->isApproved());
        $this->assertEquals('Dies ist ein Kommentar', $vote->getComment());
        $this->assertInstanceOf(DateTime::class, $vote->getCreatedAt());
    }

    public function testIsApproved(): void
    {
        $approveVote = new Vote(1, 2, true);
        $rejectVote = new Vote(1, 2, false);

        $this->assertTrue($approveVote->isApproved());
        $this->assertFalse($rejectVote->isApproved());
    }

    public function testGetComment(): void
    {
        $voteWithComment = new Vote(1, 2, true, 'Mein Kommentar');
        $voteWithoutComment = new Vote(1, 2, false);

        $this->assertEquals('Mein Kommentar', $voteWithComment->getComment());
        $this->assertNull($voteWithoutComment->getComment());
    }
}
