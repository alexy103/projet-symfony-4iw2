<?php

namespace App\Controller;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ProfessionalExcuse;
use App\Entity\User;
use App\Form\ExcuseCommentType;
use App\Repository\ExcuseCommentRepository;
use App\Repository\ExcuseRatingRepository;
use App\Repository\ExcuseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/excuses-apercu')]
#[IsGranted('ROLE_USER')]
final class ExcusePreviewController extends AbstractController
{
    #[Route(name: 'app_excuse_preview_index', methods: ['GET'])]
    public function index(ExcuseRepository $excuseRepository): Response
    {
        $criteria = $this->isGranted('ROLE_ADMIN') ? [] : ['status' => 'validated'];
        $excuses = $excuseRepository->findBy($criteria, ['createdAt' => 'DESC']);

        return $this->render('excuse/preview_index.html.twig', [
            'excuses' => $excuses,
            'excuseTypes' => $this->buildExcuseTypes($excuses),
        ]);
    }

    #[Route('/{id}', name: 'app_excuse_preview_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Excuse $excuse, ExcuseCommentRepository $commentRepository, ExcuseRatingRepository $ratingRepository): Response
    {
        if ('validated' !== $excuse->getStatus() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        /** @var User|null $user */
        $user = $this->getUser();

        return $this->render('excuse/preview_show.html.twig', [
            'excuse' => $excuse,
            'excuseType' => $this->resolveType($excuse),
            'comments' => $commentRepository->findForExcuse($excuse),
            'comment_form' => $this->createForm(ExcuseCommentType::class)->createView(),
            'rating_stats' => $ratingRepository->getStatsForExcuse($excuse),
            'user_rating' => null !== $user ? $ratingRepository->findOneByExcuseAndAuthor($excuse, $user) : null,
        ]);
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
                $types[$id] = $this->resolveType($excuse);
            }
        }

        return $types;
    }
}
