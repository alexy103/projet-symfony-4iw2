<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notification')]
#[IsGranted('ROLE_USER')]
final class NotificationController extends AbstractController
{
    #[Route(name: 'app_notification_index', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('notification/index.html.twig', [
            'notifications' => $notificationRepository->findForUser($user),
        ]);
    }

    #[Route('/read-all', name: 'app_notification_read_all', methods: ['POST'])]
    public function readAll(Request $request, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('read-all', $request->getPayload()->getString('_token'))) {
            foreach ($notificationRepository->findForUser($user) as $notification) {
                $notification->setIsRead(true);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_notification_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/read', name: 'app_notification_read', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function read(Request $request, Notification $notification, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessOwner($notification);

        if ($this->isCsrfTokenValid('read'.$notification->getId(), $request->getPayload()->getString('_token'))) {
            $notification->setIsRead(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_notification_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_notification_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Notification $notification, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessOwner($notification);

        if ($this->isCsrfTokenValid('delete'.$notification->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($notification);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_notification_index', [], Response::HTTP_SEE_OTHER);
    }

    private function denyAccessUnlessOwner(Notification $notification): void
    {
        if ($notification->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
    }
}
