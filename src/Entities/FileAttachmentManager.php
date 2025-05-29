<?php

class Attachment
{
    private $id;
    private $applicationId;
    private $filename;
    private $filepath;
    private $mimeType;
    private $filesize;
    private $uploadedAt;

    public function __construct(
        int $applicationId,
        string $filename,
        string $filepath,
        string $mimeType,
        int $filesize
    ) {
        $this->applicationId = $applicationId;
        $this->filename = $filename;
        $this->filepath = $filepath;
        $this->mimeType = $mimeType;
        $this->filesize = $filesize;
        $this->uploadedAt = new DateTime();
    }

    public function isValidFileType(): bool
    {
        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        return in_array($this->mimeType, $allowedTypes);
    }
}
