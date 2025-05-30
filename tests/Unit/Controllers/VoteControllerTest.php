<?php

namespace Approvio\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use VoteController;
use Vote;
use User;
use Application;
use UnauthorizedException;
use NotFoundException;
use InvalidOperationException;
use DuplicateVoteException;

class VoteControllerTest extends TestCase
{
    private $voteRepository;
    private $applicationRepository;
    private $applicationController;
    private $voteController;
    private $boardMemberUser;
    private $regularUser;

    protected function setUp(): void
    {
        $this->voteRepository = $this->createMock('VoteRepository');
        $this->applicationRepository = $this->createMock('ApplicationRepository');
        $this->applicationController = $this->createMock('ApplicationController');

        $this->voteController = new VoteController(
            $this->voteRepository,
            $this->applicationRepository,
            $this->applicationController
        );

        // Test-Benutzer erstellen
        $this->boardMemberUser = $this->createMock(User::class);
        $this->boardMemberUser->method('canVote')->willReturn(true);
        $this->boardMemberUser->method('getId')->willReturn(1);
        $this->boardMemberUser->method('isAdmin')->willReturn(false);
        $this->boardMemberUser->method('isBoardMember')->willReturn(true);

        $this->regularUser = $this->createMock(User::class);
        $this->regularUser->method('canVote')->willReturn(false);
        $this->regularUser->method('isAdmin')->willReturn(false);
        $this->regularUser->method('isBoardMember')->willReturn(false);
    }

    public function testCreateVoteByBoardMember(): void
    {
        $applicationId = 1;
        $isApproved = true;
        $comment = 'Test-Kommentar';

        $application = $this->createMock(Application::class);
        $application->method('getStatus')->willReturn(Application::STATUS_PENDING);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->voteRepository
            ->expects($this->once())
            ->method('findByUserAndApplication')
            ->with(1, $applicationId)
            ->willReturn(null);

        $savedVote = $this->createMock(Vote::class);

        $this->voteRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($savedVote);

        $application->expects($this->once())
            ->method('addVote');

        $this->applicationController
            ->expects($this->once())
            ->method('updateApplicationStatus')
            ->with($applicationId);

        $result = $this->voteController->createVote(
            $this->boardMemberUser,
            $applicationId,
            $isApproved,
            $comment
        );

        $this->assertSame($savedVote, $result);
    }

    public function testCreateVoteByUnauthorizedUser(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->voteController->createVote(
            $this->regularUser,
            1,
            true,
            'Kommentar'
        );
    }

    public function testCreateVoteForNonExistentApplication(): void
    {
        $this->expectException(NotFoundException::class);

        $applicationId = 999; // Nicht existierende ID

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn(null);

        $this->voteController->createVote(
            $this->boardMemberUser,
            $applicationId,
            true
        );
    }

    public function testCreateVoteForNonPendingApplication(): void
    {
        $this->expectException(InvalidOperationException::class);

        $applicationId = 1;

        $application = $this->createMock(Application::class);
        $application->method('getStatus')->willReturn(Application::STATUS_APPROVED);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $this->voteController->createVote(
            $this->boardMemberUser,
            $applicationId,
            true
        );
    }

    public function testCreateDuplicateVote(): void
    {
        $this->expectException(DuplicateVoteException::class);

        $applicationId = 1;
        $userId = 1;

        $application = $this->createMock(Application::class);
        $application->method('getStatus')->willReturn(Application::STATUS_PENDING);

        $this->applicationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($applicationId)
            ->willReturn($application);

        $existingVote = $this->createMock(Vote::class);

        $this->voteRepository
            ->expects($this->once())
            ->method('findByUserAndApplication')
            ->with($userId, $applicationId)
            ->willReturn($existingVote);

        $this->voteController->createVote(
            $this->boardMemberUser,
            $applicationId,
            true
        );
    }

    public function testListVotesByApplicationAsBoardMember(): void
    {
        $applicationId = 1;
        $votes = [
            $this->createMock(Vote::class),
            $this->createMock(Vote::class)
        ];

        $this->voteRepository
            ->expects($this->once())
            ->method('findByApplicationId')
            ->with($applicationId)
            ->willReturn($votes);

        $result = $this->voteController->listVotesByApplication(
            $this->boardMemberUser,
            $applicationId
        );

        $this->assertSame($votes, $result);
    }

    public function testListVotesByApplicationAsUnauthorizedUser(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->voteController->listVotesByApplication(
            $this->regularUser,
            1
        );
    }

    public function testGetVoteSummaryAsBoardMember(): void
    {
        $applicationId = 1;

        $vote1 = $this->createMock(Vote::class);
        $vote1->method('isApproved')->willReturn(true);
        $vote1->method('getComment')->willReturn('Zustimmender Kommentar');
        $vote1->method('getUserId')->willReturn(1);
        $vote1->method('getCreatedAt')->willReturn(new \DateTime());

        $vote2 = $this->createMock(Vote::class);
        $vote2->method('isApproved')->willReturn(false);
        $vote2->method('getComment')->willReturn('Ablehnender Kommentar');
        $vote2->method('getUserId')->willReturn(2);
        $vote2->method('getCreatedAt')->willReturn(new \DateTime());

        $votes = [$vote1, $vote2];

        $this->voteRepository
            ->expects($this->once())
            ->method('findByApplicationId')
            ->with($applicationId)
            ->willReturn($votes);

        $result = $this->voteController->getVoteSummary(
            $this->boardMemberUser,
            $applicationId
        );

        $this->assertIsArray($result);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['approved']);
        $this->assertEquals(1, $result['rejected']);
        $this->assertCount(2, $result['comments']);
    }

    public function testGetVoteSummaryAsUnauthorizedUser(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->voteController->getVoteSummary(
            $this->regularUser,
            1
        );
    }
}
