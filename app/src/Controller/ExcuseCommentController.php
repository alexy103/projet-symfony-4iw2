<?php

namespace App\Controller;

use App\Entity\Excuse;
use App\Entity\ExcuseComment;
use App\Entity\User;
use App\Form\ExcuseCommentType;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ExcuseCommentController extends AbstractController
{
    #[Route('/excuse/{id}/comment', name: 'app_excuse_comment_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, Excuse $excuse, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        if ('validated' !== $excuse->getStatus() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        $comment = new ExcuseComment();
        $form = $this->createForm(ExcuseCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setExcuse($excuse);
            $comment->setAuthor($user);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            $author = $excuse->getAuthor();
            if (null !== $author && $author->getId() !== $user->getId()) {
                $notificationService->notify(
                    $author,
                    'Nouveau commentaire',
                    sprintf('Ton excuse « %s » a recu un nouveau commentaire.', $excuse->getTitle())
                );
            }

            $this->addFlash('success', 'Commentaire ajouté.');
        } else {
            $this->addFlash('error', 'Le commentaire n\'a pas pu être ajouté.');
        }

        return $this->redirectToExcuse($request, $excuse);
    }

    private function redirectToExcuse(Request $request, Excuse $excuse): Response
    {
        $referer = $request->headers->get('referer');
        if (null !== $referer && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_excuse_preview_show', ['id' => $excuse->getId()], Response::HTTP_SEE_OTHER);
    }
}
