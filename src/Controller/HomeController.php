<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
#[Route('/', name: 'app_home', methods: ['GET'])]
public function index(BookRepository $bookRepository, Request $request): Response
{
    $mot = $request->query->get('recherche');

    if ($mot) {
        $books = $bookRepository->findBySearch($mot);
    } else {
        $books = $bookRepository->findAll();
    }

    return $this->render('home/index.html.twig', [
        'books' => $books,
    ]);
}
    #[Route('/book/{id}/show', name: 'app_home_book_show', methods: ['GET'])]
    public function showBook(Book $book): Response
    {
        return $this->render('home/show.html.twig', [
            'book'=>$book
        ]);
    }
}