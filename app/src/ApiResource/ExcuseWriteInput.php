<?php

namespace App\ApiResource;

use Symfony\Component\Serializer\Attribute\Groups;

final class ExcuseWriteInput
{
    #[Groups(['excuse:write'])]
    public ?string $type = null;

    #[Groups(['excuse:write'])]
    public ?string $title = null;

    #[Groups(['excuse:write'])]
    public ?string $content = null;

    #[Groups(['excuse:write'])]
    public ?int $urgencyLevel = null;

    #[Groups(['excuse:write'])]
    public ?int $credibilityScore = null;

    #[Groups(['excuse:write'])]
    public ?int $authorId = null;

    #[Groups(['excuse:write'])]
    public ?int $categoryId = null;

    #[Groups(['excuse:write'])]
    public ?int $contextId = null;

    #[Groups(['excuse:write'])]
    public ?int $toneId = null;

    /**
     * @var list<int>|null
     */
    #[Groups(['excuse:write'])]
    public ?array $tagIds = null;

    #[Groups(['excuse:write'])]
    public ?int $estimatedDelay = null;

    #[Groups(['excuse:write'])]
    public ?bool $isReusable = null;

    #[Groups(['excuse:write'])]
    public ?int $emergencyLevel = null;

    #[Groups(['excuse:write'])]
    public ?bool $requiresProof = null;

    #[Groups(['excuse:write'])]
    public ?string $targetRecipient = null;

    #[Groups(['excuse:write'])]
    public ?string $professionalTone = null;
}

