<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ExcuseOutput;
use App\Repository\ExcuseRepository;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class ExcuseItemProvider implements ProviderInterface
{
    public function __construct(
        private ExcuseRepository $excuseRepository,
        private Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ExcuseOutput
    {
        $id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : 0;
        if ($id <= 0) {
            return null;
        }

        $excuse = $this->excuseRepository->find($id);

        if (null === $excuse) {
            return null;
        }

        if ('validated' !== $excuse->getStatus() && !$this->security->isGranted('ROLE_ADMIN')) {
            return null;
        }

        return ExcuseOutput::fromEntity($excuse);
    }
}

