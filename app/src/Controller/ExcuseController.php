<?php

namespace App\Controller;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ProfessionalExcuse;
use App\Entity\User;
use App\Form\ExcuseType;
use App\Repository\ExcuseRepository;
use App\Repository\ExcuseValidationRepository;
use App\Security\Voter\ExcuseVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ExcuseController extends AbstractController
{
    #[Route('/excuses', name: 'app_excuse_index', methods: ['GET'])]
    public function index(Request $request, ExcuseRepository $excuseRepository): Response
    {
        $filters = [
            'status' => $request->query->get('status', ''),
            'keyword' => $request->query->get('q', ''),
            'categoryId' => $request->query->get('categoryId', ''),
            'contextId' => $request->query->get('contextId', ''),
            'toneId' => $request->query->get('toneId', ''),
        ];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $filters['status'] = 'validated';
        }

        return $this->render('excuse/index.html.twig', [
            'excuses' => $excuseRepository->findByFilters($filters),
            'filters' => $filters,
        ]);
    }

    #[Route('/my-excuses', name: 'app_my_excuses', methods: ['GET'])]
    public function myExcuses(ExcuseRepository $excuseRepository, ExcuseValidationRepository $validationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $excuses = $excuseRepository->findUserExcuses($user);

        return $this->render('excuse/my_excuses.html.twig', [
            'excuses' => $excuses,
            'rejectionReasons' => $this->buildRejectionReasons($excuses, $validationRepository),
        ]);
    }

    #[Route('/excuses/new', name: 'app_excuse_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('excuse/new.html.twig');
    }

    #[Route('/excuses/new/{type}', name: 'app_excuse_new_type', methods: ['GET', 'POST'], requirements: ['type' => 'classic|emergency|professional'])]
    public function newByType(string $type, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $excuse = $this->createExcuseByType($type);
        $excuse->setAuthor($user);
        $excuse->setStatus('pending');
        $excuse->setCreatedAt(new \DateTimeImmutable());
        $excuse->setUpdatedAt(null);

        $form = $this->createForm(ExcuseType::class, $excuse, [
            'excuse_type' => $type,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($excuse);
            $entityManager->flush();

            $this->addFlash('success', 'Excuse créée et soumise à validation.');

            return $this->redirectToRoute('app_my_excuses');
        }

        return $this->render('excuse/create.html.twig', [
            'form' => $form,
            'type' => $type,
        ]);
    }

    #[Route('/excuses/{id}', name: 'app_excuse_show', methods: ['GET'])]
    public function show(Excuse $excuse, ExcuseValidationRepository $validationRepository): Response
    {
        $this->denyAccessUnlessGranted(ExcuseVoter::EXCUSE_VIEW, $excuse);

        $rejectionReasons = $this->buildRejectionReasons([$excuse], $validationRepository);

        return $this->render('excuse/show.html.twig', [
            'excuse' => $excuse,
            'rejectionReason' => $rejectionReasons[$excuse->getId() ?? 0] ?? null,
        ]);
    }

    #[Route('/excuses/{id}/edit', name: 'app_excuse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(ExcuseVoter::EXCUSE_EDIT, $excuse);

        $form = $this->createForm(ExcuseType::class, $excuse, [
            'excuse_type' => $this->resolveType($excuse),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $excuse->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Excuse modifiée.');

            return $this->redirectToRoute('app_excuse_show', ['id' => $excuse->getId()]);
        }

        return $this->render('excuse/edit.html.twig', [
            'excuse' => $excuse,
            'form' => $form,
        ]);
    }

    #[Route('/excuses/{id}/delete', name: 'app_excuse_delete', methods: ['POST'])]
    public function delete(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(ExcuseVoter::EXCUSE_DELETE, $excuse);

        if ($this->isCsrfTokenValid('delete'.$excuse->getId(), $request->getPayload()->getString('_token'))) {
            foreach ($excuse->getComments()->toArray() as $comment) {
                $excuse->removeComment($comment);
                $entityManager->remove($comment);
            }

            foreach ($excuse->getRatings()->toArray() as $rating) {
                $excuse->removeRating($rating);
                $entityManager->remove($rating);
            }

            foreach ($excuse->getValidations()->toArray() as $validation) {
                $excuse->removeValidation($validation);
                $entityManager->remove($validation);
            }

            foreach ($excuse->getTags()->toArray() as $tag) {
                $excuse->removeTag($tag);
            }

            $entityManager->remove($excuse);
            $entityManager->flush();
            $this->addFlash('success', 'Excuse supprimée.');
        }

        return $this->redirectToRoute('app_my_excuses');
    }

    #[Route('/excuses/{id}/resubmit', name: 'app_excuse_resubmit', methods: ['POST'])]
    public function resubmit(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(ExcuseVoter::EXCUSE_EDIT, $excuse);

        if (!$this->isCsrfTokenValid('resubmit'.$excuse->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ('rejected' !== $excuse->getStatus()) {
            $this->addFlash('warning', 'Seules les excuses rejetées peuvent être resoumises.');

            return $this->redirectToRoute('app_excuse_show', ['id' => $excuse->getId()]);
        }

        $excuse->setStatus('pending');
        $excuse->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $this->addFlash('success', 'Excuse resoumise à validation.');

        return $this->redirectToRoute('app_excuse_show', ['id' => $excuse->getId()]);
    }

    private function createExcuseByType(string $type): Excuse
    {
        return match ($type) {
            'classic' => (new ClassicExcuse())->setIsReusable(true)->setEstimatedDelay(null),
            'emergency' => (new EmergencyExcuse())->setEmergencyLevel(1)->setRequiresProof(false),
            'professional' => (new ProfessionalExcuse())->setTargetRecipient(null)->setProfessionalTone(null),
            default => throw $this->createNotFoundException('Type d\'excuse inconnu.'),
        };
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
    private function buildRejectionReasons(array $excuses, ExcuseValidationRepository $validationRepository): array
    {
        $excuseIds = [];
        foreach ($excuses as $excuse) {
            $id = $excuse->getId();
            if (null !== $id) {
                $excuseIds[] = $id;
            }
        }

        if ([] === $excuseIds) {
            return [];
        }

        return $validationRepository->findLatestRejectedCommentsByExcuseIds(array_values(array_unique($excuseIds)));
    }
}

