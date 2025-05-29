<?php

class Application
{
    private $id;
    private $title;
    private $description;
    private $requestedAmount;
    private $applicantEmail;
    private $status;
    private $createdAt;
    private $attachments;
    private $votes;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function __construct(
        string $title,
        string $description,
        float $requestedAmount,
        string $applicantEmail
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->requestedAmount = $requestedAmount;
        $this->applicantEmail = $applicantEmail;
        $this->status = self::STATUS_PENDING;
        $this->createdAt = new DateTime();
        $this->attachments = [];
        $this->votes = [];
    }

    public function addAttachment(Attachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    public function addVote(Vote $vote): void
    {
        $this->votes[] = $vote;
        $this->updateStatus();
    }

    private function updateStatus(): void
    {
        $approvalCount = 0;
        $rejectionCount = 0;

        foreach ($this->votes as $vote) {
            if ($vote->isApproved()) {
                $approvalCount++;
            } else {
                $rejectionCount++;
            }
        }

        // Mehrheitsentscheidung (einfache Mehrheit)
        if ($approvalCount > $rejectionCount) {
            $this->status = self::STATUS_APPROVED;
        } elseif ($rejectionCount > $approvalCount) {
            $this->status = self::STATUS_REJECTED;
        }
    }
}