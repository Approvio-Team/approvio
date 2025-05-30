<?php

namespace Approvio\Tests\Unit\Entities;

use PHPUnit\Framework\TestCase;
use User;

class UserTest extends TestCase
{
    public function testConstructorWithDefaultRole(): void
    {
        $user = new User('user@example.com', 'Test User');

        $this->assertEquals('user@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals(User::ROLE_BOARD_MEMBER, $user->getRole());
        $this->assertTrue($user->isActive());
    }

    public function testConstructorWithAdminRole(): void
    {
        $user = new User('admin@example.com', 'Admin User', User::ROLE_ADMIN);

        $this->assertEquals('admin@example.com', $user->getEmail());
        $this->assertEquals('Admin User', $user->getName());
        $this->assertEquals(User::ROLE_ADMIN, $user->getRole());
        $this->assertTrue($user->isActive());
    }

    public function testIsAdmin(): void
    {
        $admin = new User('admin@example.com', 'Admin User', User::ROLE_ADMIN);
        $boardMember = new User('board@example.com', 'Board Member', User::ROLE_BOARD_MEMBER);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($boardMember->isAdmin());
    }

    public function testIsBoardMember(): void
    {
        $admin = new User('admin@example.com', 'Admin User', User::ROLE_ADMIN);
        $boardMember = new User('board@example.com', 'Board Member', User::ROLE_BOARD_MEMBER);

        $this->assertFalse($admin->isBoardMember());
        $this->assertTrue($boardMember->isBoardMember());
    }

    public function testCanVote(): void
    {
        // Aktiver Board Member kann abstimmen
        $activeBoardMember = new User('board@example.com', 'Board Member');
        $this->assertTrue($activeBoardMember->canVote());

        // Admin kann nicht abstimmen (gemäß Implementierung)
        $admin = new User('admin@example.com', 'Admin User', User::ROLE_ADMIN);
        $this->assertFalse($admin->canVote());

        // Inaktiver Board Member kann nicht abstimmen
        $inactiveBoardMember = new User('inactive@example.com', 'Inactive Member');
        $inactiveBoardMember->setActive(false);
        $this->assertFalse($inactiveBoardMember->canVote());
    }

    public function testSetActive(): void
    {
        $user = new User('user@example.com', 'Test User');
        $this->assertTrue($user->isActive());

        $user->setActive(false);
        $this->assertFalse($user->isActive());

        $user->setActive(true);
        $this->assertTrue($user->isActive());
    }

    public function testSetName(): void
    {
        $user = new User('user@example.com', 'Old Name');
        $this->assertEquals('Old Name', $user->getName());

        $user->setName('New Name');
        $this->assertEquals('New Name', $user->getName());
    }

    public function testSetRole(): void
    {
        $user = new User('user@example.com', 'Test User');
        $this->assertEquals(User::ROLE_BOARD_MEMBER, $user->getRole());

        $user->setRole(User::ROLE_ADMIN);
        $this->assertEquals(User::ROLE_ADMIN, $user->getRole());
    }
}
