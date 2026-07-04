<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ExcuseOutput;
use App\ApiResource\ExcuseWriteInput;
use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ExcuseCategory;
use App\Entity\ExcuseContext;
use App\Entity\ExcuseTone;
use App\Entity\ProfessionalExcuse;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\ExcuseRepository;
use App\Security\Voter\ExcuseVoter;
use App\Service\CredibilityScoreService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class ExcuseWriteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExcuseRepository $excuseRepository,
        private Security $security,
        private CredibilityScoreService $credibilityScoreService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExcuseOutput
    {
        if (!$data instanceof ExcuseWriteInput) {
            throw new BadRequestHttpException('Payload invalide.');
        }

        if ($operation instanceof Post) {
            $excuse = $this->createExcuse($data);
            $this->entityManager->persist($excuse);
        } elseif ($operation instanceof Patch) {
            $excuse = $this->updateExcuse($data, $uriVariables);
        } else {
            throw new BadRequestHttpException('Operation non supportee.');
        }

        $this->entityManager->flush();

        return ExcuseOutput::fromEntity($excuse);
    }

    private function createExcuse(ExcuseWriteInput $input): Excuse
    {
        $author = $this->getAuthenticatedUser();

        $type = strtolower(trim((string) $input->type));
        if ('' === $type) {
            throw new BadRequestHttpException('Le champ "type" est obligatoire.');
        }

        $category = $this->findOrFail(ExcuseCategory::class, $input->categoryId, 'categoryId');
        $context = $this->findOrFail(ExcuseContext::class, $input->contextId, 'contextId');
        $tone = $this->findOrFail(ExcuseTone::class, $input->toneId, 'toneId');

        $excuse = $this->instantiateByType($type);
        $this->applyCommonFields($excuse, $input, true);
        $this->applyTypeSpecificFields($excuse, $input, true);

        $excuse
            ->setAuthor($author)
            ->setCategory($category)
            ->setContext($context)
            ->setTone($tone)
            ->setStatus('pending')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(null);

        $excuse->setCredibilityScore($this->credibilityScoreService->calculate($excuse));

        $this->replaceTags($excuse, $input->tagIds);

        return $excuse;
    }

    private function updateExcuse(ExcuseWriteInput $input, array $uriVariables): Excuse
    {
        $id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        if ($id <= 0) {
            throw new BadRequestHttpException('Identifiant invalide.');
        }

        $excuse = $this->excuseRepository->find($id);
        if (!$excuse instanceof Excuse) {
            throw new BadRequestHttpException('Excuse introuvable.');
        }

        if (!$this->security->isGranted(ExcuseVoter::EXCUSE_EDIT, $excuse)) {
            throw new AccessDeniedHttpException('Vous ne pouvez pas modifier cette excuse.');
        }

        if (null !== $input->type && '' !== trim($input->type)) {
            $expected = strtolower($this->resolveType($excuse));
            if (strtolower(trim($input->type)) !== $expected) {
                throw new BadRequestHttpException('Le type d\'excuse ne peut pas etre modifie.');
            }
        }

        if (null !== $input->categoryId) {
            $excuse->setCategory($this->findOrFail(ExcuseCategory::class, $input->categoryId, 'categoryId'));
        }

        if (null !== $input->contextId) {
            $excuse->setContext($this->findOrFail(ExcuseContext::class, $input->contextId, 'contextId'));
        }

        if (null !== $input->toneId) {
            $excuse->setTone($this->findOrFail(ExcuseTone::class, $input->toneId, 'toneId'));
        }

        if (null !== $input->authorId) {
            throw new BadRequestHttpException('Le champ "authorId" est gere automatiquement.');
        }

        $this->applyCommonFields($excuse, $input, false);
        $this->applyTypeSpecificFields($excuse, $input, false);
        $excuse->setCredibilityScore($this->credibilityScoreService->calculate($excuse));
        $excuse->setUpdatedAt(new \DateTimeImmutable());

        if (null !== $input->tagIds) {
            $this->replaceTags($excuse, $input->tagIds);
        }

        return $excuse;
    }

    private function instantiateByType(string $type): Excuse
    {
        return match ($type) {
            'classic' => (new ClassicExcuse())->setIsReusable(true)->setEstimatedDelay(null),
            'emergency' => (new EmergencyExcuse())->setEmergencyLevel(1)->setRequiresProof(false),
            'professional' => (new ProfessionalExcuse())->setTargetRecipient(null)->setProfessionalTone(null),
            default => throw new BadRequestHttpException('Type inconnu. Valeurs attendues: classic, emergency, professional.'),
        };
    }

    private function applyCommonFields(Excuse $excuse, ExcuseWriteInput $input, bool $isCreate): void
    {
        if ($isCreate && (null === $input->title || '' === trim($input->title))) {
            throw new BadRequestHttpException('Le champ "title" est obligatoire.');
        }

        if ($isCreate && (null === $input->content || '' === trim($input->content))) {
            throw new BadRequestHttpException('Le champ "content" est obligatoire.');
        }

        if ($isCreate && null === $input->urgencyLevel) {
            throw new BadRequestHttpException('Le champ "urgencyLevel" est obligatoire.');
        }

        if (null !== $input->title) {
            $excuse->setTitle(trim($input->title));
        }

        if (null !== $input->content) {
            $excuse->setContent(trim($input->content));
        }

        if (null !== $input->urgencyLevel) {
            $excuse->setUrgencyLevel($input->urgencyLevel);
        }

        // credibilityScore est calcule automatiquement par l'application.
    }

    private function getAuthenticatedUser(): User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Authentification requise.');
        }

        return $user;
    }

    private function applyTypeSpecificFields(Excuse $excuse, ExcuseWriteInput $input, bool $isCreate): void
    {
        if ($excuse instanceof ClassicExcuse) {
            if (null !== $input->estimatedDelay || $isCreate) {
                $excuse->setEstimatedDelay($input->estimatedDelay);
            }
            if (null !== $input->isReusable) {
                $excuse->setIsReusable($input->isReusable);
            }

            return;
        }

        if ($excuse instanceof EmergencyExcuse) {
            if ($isCreate && null === $input->emergencyLevel) {
                throw new BadRequestHttpException('Le champ "emergencyLevel" est obligatoire pour le type emergency.');
            }

            if (null !== $input->emergencyLevel) {
                $excuse->setEmergencyLevel($input->emergencyLevel);
            }

            if (null !== $input->requiresProof) {
                $excuse->setRequiresProof($input->requiresProof);
            }

            return;
        }

        if ($excuse instanceof ProfessionalExcuse) {
            if (null !== $input->targetRecipient || $isCreate) {
                $excuse->setTargetRecipient($input->targetRecipient);
            }

            if (null !== $input->professionalTone || $isCreate) {
                $excuse->setProfessionalTone($input->professionalTone);
            }
        }
    }

    /**
     * @param list<int>|null $tagIds
     */
    private function replaceTags(Excuse $excuse, ?array $tagIds): void
    {
        if (null === $tagIds) {
            return;
        }

        foreach ($excuse->getTags()->toArray() as $tag) {
            $excuse->removeTag($tag);
        }

        foreach ($tagIds as $tagId) {
            $tag = $this->findOrFail(Tag::class, (int) $tagId, 'tagIds');
            $excuse->addTag($tag);
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    private function findOrFail(string $className, ?int $id, string $field): object
    {
        if (null === $id || $id <= 0) {
            throw new BadRequestHttpException(sprintf('Le champ "%s" est obligatoire et doit etre un entier positif.', $field));
        }

        $entity = $this->entityManager->getRepository($className)->find($id);
        if (null === $entity) {
            throw new BadRequestHttpException(sprintf('Aucune ressource trouvee pour "%s" (id=%d).', $field, $id));
        }

        return $entity;
    }

    private function resolveType(Excuse $excuse): string
    {
        return match (true) {
            $excuse instanceof ClassicExcuse => 'classic',
            $excuse instanceof EmergencyExcuse => 'emergency',
            $excuse instanceof ProfessionalExcuse => 'professional',
            default => '',
        };
    }
}

