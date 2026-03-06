<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
 #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {

        return $this->render('home/index.html.twig', [
            'books'=>$bookRepository->findAll()
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