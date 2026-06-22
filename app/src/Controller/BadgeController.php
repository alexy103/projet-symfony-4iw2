<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\User;
use App\Form\BadgeType;
use App\Repository\BadgeRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/badge')]
#[IsGranted('ROLE_USER')]
final class BadgeController extends AbstractController
{
    #[Route(name: 'app_badge_index', methods: ['GET'])]
    public function index(BadgeRepository $badgeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $earnedIds = [];
        foreach ($user->getBadges() as $badge) {
            $earnedIds[] = $badge->getId();
        }

        return $this->render('badge/index.html.twig', [
            'badges' => $badgeRepository->findBy([], ['name' => 'ASC']),
            'earned_ids' => $earnedIds,
        ]);
    }

    #[Route('/new', name: 'app_badge_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $badge = new Badge();
        $form = $this->createForm(BadgeType::class, $badge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleIconUpload($form, $badge);

            $entityManager->persist($badge);
            $entityManager->flush();

            $this->addFlash('success', 'Le badge a été créé.');

            return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badge/new.html.twig', [
            'badge' => $badge,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_badge_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Badge $badge, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BadgeType::class, $badge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleIconUpload($form, $badge);

            $entityManager->flush();

            $this->addFlash('success', 'Le badge a été modifié.');

            return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badge/edit.html.twig', [
            'badge' => $badge,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badge_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Badge $badge, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$badge->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($badge);
            $entityManager->flush();

            $this->addFlash('success', 'Le badge a été supprimé.');
        }

        return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/recipients', name: 'app_badge_recipients', methods: ['GET', 'POST'])]
    public function recipients(
        Request $request,
        Badge $badge,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
    ): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_VALIDATOR')) {
            throw $this->createAccessDeniedException();
        }

        $users = $userRepository->findBy([], ['email' => 'ASC']);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $users = array_values(array_filter($users, static function (User $candidate): bool {
                $roles = $candidate->getRoles();

                return !in_array('ROLE_ADMIN', $roles, true) && !in_array('ROLE_VALIDATOR', $roles, true);
            }));
        }

        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('recipients'.$badge->getId(), $request->getPayload()->getString('_token'))) {
                $selectedIds = $request->getPayload()->all('users');
                $newRecipients = [];

                foreach ($users as $user) {
                    $hasBadge = $badge->getUsers()->contains($user);
                    $shouldHave = in_array((string) $user->getId(), $selectedIds, true);

                    if ($shouldHave && !$hasBadge) {
                        $badge->addUser($user);
                        $newRecipients[] = $user;
                    } elseif (!$shouldHave && $hasBadge) {
                        $badge->removeUser($user);
                    }
                }

                $entityManager->flush();

                foreach ($newRecipients as $recipient) {
                    $notificationService->notify(
                        $recipient,
                        'Badge débloqué',
                        sprintf('Félicitations, vous avez débloqué le badge "%s".', $badge->getName() ?? 'sans nom')
                    );
                }

                $this->addFlash('success', 'Les attributions du badge ont été mises à jour.');

                return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->render('badge/recipients.html.twig', [
            'badge' => $badge,
            'users' => $users,
        ]);
    }

    private function handleIconUpload(FormInterface $form, Badge $badge): void
    {
        /** @var UploadedFile|null $file */
        $file = $form->get('iconFile')->getData();
        if (null === $file) {
            return;
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
        $filename = uniqid('badge_', true).'.'.$extension;
        $file->move($this->getParameter('kernel.project_dir').'/public/uploads/badges', $filename);

        $badge->setIcon($filename);
    }
}
