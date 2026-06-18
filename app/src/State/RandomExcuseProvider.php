<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ExcuseOutput;
use App\Repository\ExcuseRepository;

final readonly class RandomExcuseProvider implements ProviderInterface
{
    public function __construct(private ExcuseRepository $excuseRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ExcuseOutput
    {
        $count = $this->excuseRepository->count([]);

        if (0 === $count) {
            return null;
        }

        $offset = random_int(0, $count - 1);
        $excuses = $this->excuseRepository->findBy([], ['id' => 'ASC'], 1, $offset);

        if ([] === $excuses) {
            return null;
        }

        return ExcuseOutput::fromEntity($excuses[0]);
    }
}

