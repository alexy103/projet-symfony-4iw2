<?php

namespace App\Controller;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ProfessionalExcuse;
use App\Entity\User;
use App\Repository\ExcuseCommentRepository;
use App\Repository\ExcuseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ExcuseRepository $excuseRepository, ExcuseCommentRepository $commentRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $myExcuses = $excuseRepository->findUserExcuses($user);
        $publicExcuses = $excuseRepository->findValidatedExcuses();

        $excuseIds = [];
        foreach ($myExcuses as $excuse) {
            $id = $excuse->getId();
            if (null !== $id) {
                $excuseIds[] = $id;
            }
        }

        return $this->render('profile/index.html.twig', [
            'myExcuses' => $myExcuses,
            'publicExcuses' => $publicExcuses,
            'myExcuseCommentCounts' => $commentRepository->countByExcuseIds(array_values(array_unique($excuseIds))),
            'myExcuseTypes' => $this->buildExcuseTypes($myExcuses),
            'publicExcuseTypes' => $this->buildExcuseTypes($publicExcuses),
        ]);
    }

    /**
     * @param Excuse[] $excuses
     *
     * @return array<int, string>
     */
    private function buildExcuseTypes(array $excuses): array
    {
        $types = [];
        foreach ($excuses as $excuse) {
            $id = $excuse->getId();
            if (null !== $id) {
                $types[$id] = match (true) {
                    $excuse instanceof ClassicExcuse => 'classic',
                    $excuse instanceof EmergencyExcuse => 'emergency',
                    $excuse instanceof ProfessionalExcuse => 'professional',
                    default => '',
                };
            }
        }

        return $types;
    }
}
