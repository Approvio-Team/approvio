<?php

namespace Approvio\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use UserController;
use User;
use UnauthorizedException;
use NotFoundException;
use DuplicateUserException;

class UserControllerTest extends TestCase
{
    private $userRepository;
    private $userController;
    private $adminUser;
    private $regularUser;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock('UserRepository');
        $this->userController = new UserController($this->userRepository);

        // Test-Benutzer erstellen
        $this->adminUser = $this->createMock(User::class);
        $this->adminUser->method('isAdmin')->willReturn(true);

        $this->regularUser = $this->createMock(User::class);
        $this->regularUser->method('isAdmin')->willReturn(false);
    }

    public function testCreateUserAsAdmin(): void
    {
        $email = 'newuser@example.com';
        $name = 'New User';
        $role = User::ROLE_BOARD_MEMBER;

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $savedUser = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($savedUser);

        $result = $this->userController->createUser($this->adminUser, $email, $name, $role);

        $this->assertSame($savedUser, $result);
    }

    public function testCreateUserAsNonAdmin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->userController->createUser(
            $this->regularUser,
            'newuser@example.com',
            'New User'
        );
    }

    public function testCreateUserWithExistingEmail(): void
    {
        $this->expectException(DuplicateUserException::class);

        $email = 'existing@example.com';
        $existingUser = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($existingUser);

        $this->userController->createUser($this->adminUser, $email, 'New User');
    }

    public function testUpdateUserAsAdmin(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);
        $data = ['name' => 'Updated Name', 'role' => User::ROLE_ADMIN, 'isActive' => false];

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects($this->once())
            ->method('setName')
            ->with('Updated Name');

        $user->expects($this->once())
            ->method('setRole')
            ->with(User::ROLE_ADMIN);

        $user->expects($this->once())
            ->method('setActive')
            ->with(false);

        $savedUser = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user)
            ->willReturn($savedUser);

        $result = $this->userController->updateUser($this->adminUser, $userId, $data);

        $this->assertSame($savedUser, $result);
    }

    public function testUpdateUserAsNonAdmin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->userController->updateUser($this->regularUser, 1, ['name' => 'New Name']);
    }

    public function testUpdateUserThatDoesNotExist(): void
    {
        $this->expectException(NotFoundException::class);

        $userId = 999; // Nicht existierende ID

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $this->userController->updateUser($this->adminUser, $userId, ['name' => 'New Name']);
    }

    public function testGetUserAsAdmin(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->userController->getUser($this->adminUser, $userId);

        $this->assertSame($user, $result);
    }

    public function testGetUserAsNonAdmin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->userController->getUser($this->regularUser, 1);
    }

    public function testGetUserThatDoesNotExist(): void
    {
        $this->expectException(NotFoundException::class);

        $userId = 999; // Nicht existierende ID

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $this->userController->getUser($this->adminUser, $userId);
    }

    public function testListUsersAsAdmin(): void
    {
        $users = [
            $this->createMock(User::class),
            $this->createMock(User::class)
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($users);

        $result = $this->userController->listUsers($this->adminUser);

        $this->assertSame($users, $result);
    }

    public function testListUsersAsNonAdmin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->userController->listUsers($this->regularUser);
    }

    public function testDeactivateUserAsAdmin(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects($this->once())
            ->method('setActive')
            ->with(false);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user)
            ->willReturn($user);

        $result = $this->userController->deactivateUser($this->adminUser, $userId);

        $this->assertSame($user, $result);
    }

    public function testDeactivateUserAsNonAdmin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->userController->deactivateUser($this->regularUser, 1);
    }

    public function testDeactivateUserThatDoesNotExist(): void
    {
        $this->expectException(NotFoundException::class);

        $userId = 999; // Nicht existierende ID

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $this->userController->deactivateUser($this->adminUser, $userId);
    }

    public function testActivateUserAsAdmin(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects($this->once())
            ->method('setActive')
            ->with(true);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user)
            ->willReturn($user);

        $result = $this->userController->activateUser($this->adminUser, $userId);

        $this->assertSame($user, $result);
    }

    public function testActivateUserAsNonAdmin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->userController->activateUser($this->regularUser, 1);
    }

    public function testActivateUserThatDoesNotExist(): void
    {
        $this->expectException(NotFoundException::class);

        $userId = 999; // Nicht existierende ID

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $this->userController->activateUser($this->adminUser, $userId);
    }
}
