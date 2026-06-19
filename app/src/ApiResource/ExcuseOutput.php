<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Excuse;
use App\State\ExcuseWriteProcessor;
use App\State\ExcuseItemProvider;
use App\State\RandomExcuseProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'Excuse',
    operations: [
        new Get(
            uriTemplate: '/v1/excuses/{id}',
            requirements: ['id' => '\\d+'],
            provider: ExcuseItemProvider::class,
            normalizationContext: ['groups' => ['excuse:read', 'user:read', 'tag:read']]
        ),
        new Get(
            uriTemplate: '/v1/excuses/random',
            provider: RandomExcuseProvider::class,
            normalizationContext: ['groups' => ['excuse:read', 'user:read', 'tag:read']]
        ),
        new Post(
            uriTemplate: '/v1/excuses',
            read: false,
            input: ExcuseWriteInput::class,
            processor: ExcuseWriteProcessor::class,
            normalizationContext: ['groups' => ['excuse:read', 'user:read', 'tag:read']],
            denormalizationContext: ['groups' => ['excuse:write']]
        ),
        new Patch(
            uriTemplate: '/v1/excuses/{id}',
            requirements: ['id' => '\\d+'],
            read: false,
            input: ExcuseWriteInput::class,
            processor: ExcuseWriteProcessor::class,
            normalizationContext: ['groups' => ['excuse:read', 'user:read', 'tag:read']],
            denormalizationContext: ['groups' => ['excuse:write']]
        ),
    ]
)]
final class ExcuseOutput
{
    #[ApiProperty(identifier: true)]
    #[Groups(['excuse:read'])]
    public ?int $id = null;

    #[Groups(['excuse:read'])]
    public ?string $title = null;

    #[Groups(['excuse:read'])]
    public ?string $content = null;

    #[Groups(['excuse:read'])]
    public ?string $status = null;

    #[Groups(['excuse:read'])]
    public ?int $urgencyLevel = null;

    #[Groups(['excuse:read'])]
    public ?int $credibilityScore = null;

    #[Groups(['excuse:read'])]
    public ?string $type = null;

    #[Groups(['user:read'])]
    public ?string $authorEmail = null;

    /**
     * @var list<string>
     */
    #[Groups(['tag:read'])]
    public array $tags = [];

    public static function fromEntity(Excuse $excuse): self
    {
        $output = new self();
        $output->id = $excuse->getId();
        $output->title = $excuse->getTitle();
        $output->content = $excuse->getContent();
        $output->status = $excuse->getStatus();
        $output->urgencyLevel = $excuse->getUrgencyLevel();
        $output->credibilityScore = $excuse->getCredibilityScore();
        $output->type = (new \ReflectionClass($excuse))->getShortName();
        $output->authorEmail = $excuse->getAuthor()?->getEmail();

        foreach ($excuse->getTags() as $tag) {
            $name = $tag->getName();
            if (null !== $name) {
                $output->tags[] = $name;
            }
        }

        return $output;
    }
}


