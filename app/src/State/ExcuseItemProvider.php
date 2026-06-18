<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ExcuseOutput;
use App\Repository\ExcuseRepository;

final readonly class ExcuseItemProvider implements ProviderInterface
{
    public function __construct(private ExcuseRepository $excuseRepository)
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

        return ExcuseOutput::fromEntity($excuse);
    }
}

