<?php

namespace App\Controller;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ExcuseValidation;
use App\Entity\ProfessionalExcuse;
use App\Entity\User;
use App\Repository\ExcuseRepository;
use App\Security\Voter\ExcuseVoter;
use App\Service\NotificationService;
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

        $excuses = $excuseRepository->findPendingExcuses();

        return $this->render('validator/excuses.html.twig', [
            'excuses' => $excuses,
            'excuseTypes' => $this->buildExcuseTypes($excuses),
        ]);
    }

    #[Route('/{id}/accept', name: 'app_validator_excuse_accept', methods: ['POST'])]
    public function accept(Request $request, Excuse $excuse, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
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

        $author = $excuse->getAuthor();
        if (null !== $author) {
            $notificationService->notify(
                $author,
                'Excuse validée',
                sprintf('Ton excuse « %s » a été acceptée par un validateur.', $excuse->getTitle())
            );
        }

        $this->addFlash('success', 'Excuse acceptée.');

        return $this->redirectToRoute('app_validator_excuses');
    }

    #[Route('/{id}/reject', name: 'app_validator_excuse_reject', methods: ['POST'])]
    public function reject(Request $request, Excuse $excuse, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
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

        $author = $excuse->getAuthor();
        if (null !== $author) {
            $message = sprintf('Ton excuse « %s » a été refusée par un validateur.', $excuse->getTitle());
            if (null !== $validation->getComment()) {
                $message .= sprintf(' Motif : %s', $validation->getComment());
            }
            $notificationService->notify($author, 'Excuse refusée', $message);
        }

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

