<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Emprunt;
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
    public function new(
        Book $book,
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ): Response {
        if ($book->getStock() < 0) {
            $this->addFlash('danger', 'Livre non disponible');

            return $this->redirectToRoute('app_book_index');
        }
        $emprunt = new Emprunt();
        $emprunt->setBook($book);
        $emprunt->setDateEmprunt(new \DateTimeImmutable());
        $emprunt->setStatut('en_cours');

        $form = $this->createForm(EmpruntType::class, $emprunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setStock($book->getStock() - 1);

            $entityManagerInterface->persist($emprunt);
            $entityManagerInterface->flush();

            $this->addFlash('success', "Emprunt enregistré !");

            return $this->redirectToRoute('app_book_index');
        }
        return $this->render('emprunt/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }
}