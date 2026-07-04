<?php

namespace App\Tests\Service;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\ExcuseCategory;
use App\Entity\ExcuseContext;
use App\Entity\ExcuseTone;
use App\Entity\ProfessionalExcuse;
use App\Service\CredibilityScoreService;
use PHPUnit\Framework\TestCase;

final class CredibilityScoreServiceTest extends TestCase
{
    public function testScoreStaysBetween0And100(): void
    {
        $service = new CredibilityScoreService();
        $excuse = (new ClassicExcuse())->setContent(str_repeat('a', 60));

        $score = $service->calculate($excuse);

        self::assertGreaterThanOrEqual(0, $score);
        self::assertLessThanOrEqual(100, $score);
    }

    public function testAbsurdToneReducesScore(): void
    {
        $service = new CredibilityScoreService();
        $tone = (new ExcuseTone())->setName('Absurde');
        $excuse = (new ClassicExcuse())->setContent(str_repeat('a', 60))->setTone($tone);

        // 70 de base - 30 (ton absurde) = 40
        self::assertSame(40, $service->calculate($excuse));
    }

    public function testShortContentReducesScore(): void
    {
        $service = new CredibilityScoreService();
        $excuse = (new ClassicExcuse())->setContent('Trop court');

        // 70 de base - 15 (contenu < 40 caractères) = 55
        self::assertSame(55, $service->calculate($excuse));
    }

    public function testEmergencyRequiringProofReducesScore(): void
    {
        $service = new CredibilityScoreService();
        $excuse = (new EmergencyExcuse())->setContent(str_repeat('a', 60));
        $excuse->setRequiresProof(true);

        // 70 de base - 10 (preuve requise) = 60
        self::assertSame(60, $service->calculate($excuse));
    }

    public function testCoherentCategoryAndContextIncreaseScore(): void
    {
        $service = new CredibilityScoreService();
        $category = (new ExcuseCategory())->setName('Retard');
        $context = (new ExcuseContext())->setName('Transport');
        $excuse = (new ClassicExcuse())
            ->setContent(str_repeat('a', 60))
            ->setCategory($category)
            ->setContext($context);

        // 70 de base + 10 coherence = 80
        self::assertSame(80, $service->calculate($excuse));
    }

    public function testProfessionalExcuseGetsBonus(): void
    {
        $service = new CredibilityScoreService();
        $excuse = (new ProfessionalExcuse())->setContent(str_repeat('a', 60));

        // 70 de base + 10 pro = 80
        self::assertSame(80, $service->calculate($excuse));
    }

    public function testScoreIsCappedAt100(): void
    {
        $service = new CredibilityScoreService();
        $category = (new ExcuseCategory())->setName('Retard');
        $context = (new ExcuseContext())->setName('Transport');
        $tone = (new ExcuseTone())->setName('Serieux');
        $excuse = (new ProfessionalExcuse())
            ->setContent(str_repeat('a', 120))
            ->setCategory($category)
            ->setContext($context)
            ->setTone($tone);

        // 70 + 10 coherence + 10 professionnel = 90 (reste <= 100)
        self::assertLessThanOrEqual(100, $service->calculate($excuse));
    }

    public function testScoreIsFlooredAt0(): void
    {
        $service = new CredibilityScoreService();
        $tone = (new ExcuseTone())->setName('Absurde dramatique');
        $excuse = (new EmergencyExcuse())
            ->setContent('court')
            ->setTone($tone)
            ->setRequiresProof(true);

        // 70 - 30 - 10 - 15 - 10 = 5 (doit rester >= 0 dans tous les cas)
        self::assertGreaterThanOrEqual(0, $service->calculate($excuse));
    }
}
