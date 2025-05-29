<?php

class User
{
    private $id;
    private $email;
    private $name;
    private $role;
    private $isActive;

    const ROLE_ADMIN = 'admin';
    const ROLE_BOARD_MEMBER = 'board_member';

    public function __construct(
        string $email,
        string $name,
        string $role = self::ROLE_BOARD_MEMBER
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->role = $role;
        $this->isActive = true;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBoardMember(): bool
    {
        return $this->role === self::ROLE_BOARD_MEMBER;
    }

    public function canVote(): bool
    {
        return $this->isActive && $this->isBoardMember();
    }
}
