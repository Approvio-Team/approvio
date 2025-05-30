<?php

class Vote
{
    private $id;
    private $userId;
    private $applicationId;
    private $isApproved;
    private $comment;
    private $createdAt;

    public function __construct(
        int $userId,
        int $applicationId,
        bool $isApproved,
        ?string $comment = null
    ) {
        $this->userId = $userId;
        $this->applicationId = $applicationId;
        $this->isApproved = $isApproved;
        $this->comment = $comment;
        $this->createdAt = new DateTime();
    }

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
