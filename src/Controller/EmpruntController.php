<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Emprunt;
use App\Entity\User;
use App\Form\EmpruntType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EmpruntController extends AbstractController
{
    #[Route('/emprunt', name: 'app_emprunt')]
    public function index(): Response
    {
        // $emprunts = $empruntRepository->findAll();

        return $this->render('emprunt/index.html.twig', [
            'controller_name' => 'Liste des Emprunts',
        ]);
    }

    #[Route('/emprunt/new/{id}', name: 'app_emprunt_new')]
    public function new(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        if ($book->getStock() <= 0) {
            $this->addFlash('danger', 'Livre non disponible');
            return $this->redirectToRoute('app_book_index');
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour emprunter un livre.');
            return $this->redirectToRoute('app_login');
        }

        $emprunt = new Emprunt();
        $emprunt->setBook($book);
        $emprunt->setUser($user);
        $emprunt->setDateEmprunt(new \DateTimeImmutable());
        $emprunt->setStatut('en_cours');

        if ($user->getPhoneNumber()) {
            $emprunt->setPhoneNumber($user->getPhoneNumber());
        }

        $form = $this->createForm(EmpruntType::class, $emprunt, [
            'is_logged_in' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $phoneDuFormulaire = $form->get('phoneNumber')->getData();
            if ($phoneDuFormulaire !== $user->getPhoneNumber()) {
                $user->setPhoneNumber($phoneDuFormulaire);
                $em->persist($user);
            }
            
            $book->setStock($book->getStock() - 1);

            $em->persist($emprunt);
            $em->flush();

            $this->addFlash('success', 'Emprunt enregistré !');
            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('emprunt/new.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
            'user' => $user
        ]);
    }
}