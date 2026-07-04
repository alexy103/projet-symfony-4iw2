<?php

namespace App\Service;

use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ProfessionalExcuse;

final class CredibilityScoreService
{
    public function calculate(Excuse $excuse): int
    {
        $score = 70;

        $toneName = mb_strtolower((string) ($excuse->getTone()?->getName() ?? ''));
        if (str_contains($toneName, 'absurde')) {
            $score -= 30;
        }

        if (str_contains($toneName, 'dramatique')) {
            $score -= 10;
        }

        $categoryName = mb_strtolower((string) ($excuse->getCategory()?->getName() ?? ''));
        $contextName = mb_strtolower((string) ($excuse->getContext()?->getName() ?? ''));
        if ('' !== $categoryName && '' !== $contextName && $this->isCategoryContextCoherent($categoryName, $contextName)) {
            $score += 10;
        }

        if (mb_strlen(trim((string) $excuse->getContent())) < 40) {
            $score -= 15;
        }

        if ($excuse instanceof ProfessionalExcuse) {
            $score += 10;
        }

        if ($excuse instanceof EmergencyExcuse && true === $excuse->isRequiresProof()) {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }

    private function isCategoryContextCoherent(string $categoryName, string $contextName): bool
    {
        if (str_contains($categoryName, 'retard') && str_contains($contextName, 'transport')) {
            return true;
        }

        if (str_contains($categoryName, 'reunion') && str_contains($contextName, 'travail')) {
            return true;
        }

        if (str_contains($categoryName, 'absence') && (str_contains($contextName, 'ecole') || str_contains($contextName, 'travail'))) {
            return true;
        }

        return false;
    }
}

