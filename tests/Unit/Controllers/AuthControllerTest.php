<?php

namespace Approvio\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use AuthController;
use User;
use InvalidCredentialsException;
use InactiveUserException;

class AuthControllerTest extends TestCase
{
    private $userRepository;
    private $sessionManager;
    private $authController;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock('UserRepository');
        $this->sessionManager = $this->createMock('SessionManager');

        $this->authController = new AuthController(
            $this->userRepository,
            $this->sessionManager
        );
    }

    public function testLoginWithValidCredentials(): void
    {
        $email = 'user@example.com';
        $password = 'password123';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $user = $this->createMock(User::class);
        $user->method('isActive')->willReturn(true);
        $user->method('getPasswordHash')->willReturn($passwordHash);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->sessionManager
            ->expects($this->once())
            ->method('createSession')
            ->with($user);

        $result = $this->authController->login($email, $password);

        $this->assertSame($user, $result);
    }

    public function testLoginWithInvalidEmail(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->authController->login($email, $password);
    }

    public function testLoginWithInactiveUser(): void
    {
        $this->expectException(InactiveUserException::class);

        $email = 'inactive@example.com';
        $password = 'password123';

        $user = $this->createMock(User::class);
        $user->method('isActive')->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->authController->login($email, $password);
    }

    public function testLoginWithWrongPassword(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $email = 'user@example.com';
        $password = 'wrongpassword';
        $correctPasswordHash = password_hash('correctpassword', PASSWORD_DEFAULT);

        $user = $this->createMock(User::class);
        $user->method('isActive')->willReturn(true);
        $user->method('getPasswordHash')->willReturn($correctPasswordHash);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->authController->login($email, $password);
    }

    public function testLogout(): void
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('destroySession');

        $result = $this->authController->logout();

        $this->assertTrue($result);
    }

    public function testGetCurrentUserWhenLoggedIn(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);

        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authController->getCurrentUser();

        $this->assertSame($user, $result);
    }

    public function testGetCurrentUserWhenNotLoggedIn(): void
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn(null);

        $this->userRepository
            ->expects($this->never())
            ->method('findById');

        $result = $this->authController->getCurrentUser();

        $this->assertNull($result);
    }

    public function testIsLoggedInWhenLoggedIn(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);

        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authController->isLoggedIn();

        $this->assertTrue($result);
    }

    public function testIsLoggedInWhenNotLoggedIn(): void
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn(null);

        $result = $this->authController->isLoggedIn();

        $this->assertFalse($result);
    }

    public function testIsAdminWhenAdminLoggedIn(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);
        $user->method('isAdmin')->willReturn(true);

        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authController->isAdmin();

        $this->assertTrue($result);
    }

    public function testIsAdminWhenNonAdminLoggedIn(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);
        $user->method('isAdmin')->willReturn(false);

        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authController->isAdmin();

        $this->assertFalse($result);
    }

    public function testIsBoardMemberWhenBoardMemberLoggedIn(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);
        $user->method('isBoardMember')->willReturn(true);

        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authController->isBoardMember();

        $this->assertTrue($result);
    }

    public function testIsBoardMemberWhenNonBoardMemberLoggedIn(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);
        $user->method('isBoardMember')->willReturn(false);

        $this->sessionManager
            ->expects($this->once())
            ->method('getCurrentUserId')
            ->willReturn($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $result = $this->authController->isBoardMember();

        $this->assertFalse($result);
    }
}
