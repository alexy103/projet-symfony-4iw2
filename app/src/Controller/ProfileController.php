<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ExcuseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ExcuseRepository $excuseRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'myExcuses' => $excuseRepository->findUserExcuses($user),
            'publicExcuses' => $excuseRepository->findValidatedExcuses(),
        ]);
    }
}
