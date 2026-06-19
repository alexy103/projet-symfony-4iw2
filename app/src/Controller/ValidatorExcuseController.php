<?php

namespace App\Controller;

use App\Entity\Excuse;
use App\Entity\ExcuseValidation;
use App\Entity\User;
use App\Repository\ExcuseRepository;
use App\Security\Voter\ExcuseVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/validator/excuses')]
final class ValidatorExcuseController extends AbstractController
{
    #[Route('', name: 'app_validator_excuses', methods: ['GET'])]
    public function index(ExcuseRepository $excuseRepository): Response
    {
        $this->denyAccessUnlessValidatorOrAdmin();

        return $this->render('validator/excuses.html.twig', [
            'excuses' => $excuseRepository->findPendingExcuses(),
        ]);
    }

    #[Route('/{id}/accept', name: 'app_validator_excuse_accept', methods: ['POST'])]
    public function accept(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessValidatorOrAdmin();
        $this->denyAccessUnlessGranted(ExcuseVoter::EXCUSE_VALIDATE, $excuse);

        if (!$this->isCsrfTokenValid('accept'.$excuse->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $validator */
        $validator = $this->getUser();

        $comment = trim($request->getPayload()->getString('comment'));

        $excuse->setStatus('validated');
        $excuse->setUpdatedAt(new \DateTimeImmutable());

        $validation = new ExcuseValidation();
        $validation
            ->setExcuse($excuse)
            ->setValidator($validator)
            ->setStatus('accepted')
            ->setComment('' !== $comment ? $comment : null)
            ->setValidatedAt(new \DateTimeImmutable());

        $entityManager->persist($validation);
        $entityManager->flush();

        $this->addFlash('success', 'Excuse acceptée.');

        return $this->redirectToRoute('app_validator_excuses');
    }

    #[Route('/{id}/reject', name: 'app_validator_excuse_reject', methods: ['POST'])]
    public function reject(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessValidatorOrAdmin();
        $this->denyAccessUnlessGranted(ExcuseVoter::EXCUSE_VALIDATE, $excuse);

        if (!$this->isCsrfTokenValid('reject'.$excuse->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $validator */
        $validator = $this->getUser();

        $comment = trim($request->getPayload()->getString('comment'));

        $excuse->setStatus('rejected');
        $excuse->setUpdatedAt(new \DateTimeImmutable());

        $validation = new ExcuseValidation();
        $validation
            ->setExcuse($excuse)
            ->setValidator($validator)
            ->setStatus('rejected')
            ->setComment('' !== $comment ? $comment : null)
            ->setValidatedAt(new \DateTimeImmutable());

        $entityManager->persist($validation);
        $entityManager->flush();

        $this->addFlash('warning', 'Excuse rejetée.');

        return $this->redirectToRoute('app_validator_excuses');
    }

    private function denyAccessUnlessValidatorOrAdmin(): void
    {
        if ($this->isGranted('ROLE_VALIDATOR') || $this->isGranted('ROLE_ADMIN')) {
            return;
        }

        throw $this->createAccessDeniedException();
    }
}

