<?php

namespace App\Controller;

use App\Entity\Excuse;
use App\Entity\ExcuseRating;
use App\Entity\User;
use App\Repository\ExcuseRatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ExcuseRatingController extends AbstractController
{
    #[Route('/excuse/{id}/rating', name: 'app_excuse_rating_set', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function set(Request $request, Excuse $excuse, ExcuseRatingRepository $ratingRepository, EntityManagerInterface $entityManager): Response
    {
        if ('validated' !== $excuse->getStatus() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('rate'.$excuse->getId(), $request->getPayload()->getString('_token'))) {
            $score = $request->getPayload()->getInt('score');

            if ($score >= 1 && $score <= 5) {
                $rating = $ratingRepository->findOneByExcuseAndAuthor($excuse, $user);

                if (null === $rating) {
                    $rating = new ExcuseRating();
                    $rating->setExcuse($excuse);
                    $rating->setAuthor($user);
                    $rating->setCreatedAt(new \DateTimeImmutable());
                    $entityManager->persist($rating);
                }

                $rating->setScore($score);
                $entityManager->flush();

                $this->addFlash('success', 'Votre note a été enregistrée.');
            } else {
                $this->addFlash('error', 'Note invalide.');
            }
        }

        $referer = $request->headers->get('referer');
        if (null !== $referer && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_excuse_preview_show', ['id' => $excuse->getId()], Response::HTTP_SEE_OTHER);
    }
}
