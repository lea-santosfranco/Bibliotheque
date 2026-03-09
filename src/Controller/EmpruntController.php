<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Emprunt;
use App\Entity\User;
use App\Form\EmpruntType;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]

final class EmpruntController extends AbstractController
{
    #[Route('/emprunt', name: 'app_emprunt')]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        $emprunts = $empruntRepository->findEnCours();

        return $this->render('emprunt/index.html.twig', [
            // 'controller_name' => 'Liste des Emprunts',
            'emprunts' => $emprunts,
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
    #[Route('/emprunts/en-cours', name: 'app_emprunt_en_cours')]
    public function enCours(EmpruntRepository $empruntRepository): Response
    {
        $emprunts = $empruntRepository->findEnCours();
        return $this->render('emprunt/en_cours.html.twig', [
            'emprunts' => $emprunts,
            'count' => count($emprunts),
        ]);
    }
    #[Route('/emprunt/{id}', name: 'app_emprunt_show')]
    public function show(Emprunt $emprunt): Response
    {
        return $this->render('emprunt/show.html.twig', [
            'emprunt' => $emprunt,
        ]);
    }
    #[Route('/mon-historique/{id}', name: 'app_user_emprunts')]
    public function userHistory(User $user, EmpruntRepository $repo): Response
    {
        $emprunts = $repo->findBy(['user' => $user]);

        return $this->render('emprunt/user_index.html.twig', [
            'user' => $user,
            'emprunts' => $emprunts
        ]);
    }
    #[Route('/emprunts/user/{id}', name: 'app_emprunt_user_history')]
    public function userFullHistory(User $user, EmpruntRepository $empruntRepository): Response
    {
        // On utilise findBy pour filtrer par l'objet User
        // On trie par date pour avoir les plus récents en premier
        $emprunts = $empruntRepository->findBy(['user' => $user], ['dateEmprunt' => 'DESC']);

        return $this->render('emprunt/user_history.html.twig', [
            'user' => $user,
            'emprunts' => $emprunts,
        ]);
    }
}