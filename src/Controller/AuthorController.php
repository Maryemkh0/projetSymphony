<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }

    #[Route('/authorName/{name}', name: 'showAuthor')]
    public function showAuthor(string $name): Response
    {
        return $this->render('author/show.html.twig', ['nom' => $name]);
    }

    #[Route('/afficher', name: 'afficher')]
    public function afficher(): Response
    {
        return new Response('Hello');
    }

    // ---------- PAGE LISTE DES AUTEURS ----------
    #[Route('/list', name: 'list')]
    public function listAuthor(): Response
    {
        $authors = [
            [
                'id' => 1,
                'picture' => 'assets/images/enginer.jpg',
                'username' => 'Victor Hugo',
                'email' => 'victor.hugo@gmail.com',
                'nb_books' => 100
            ],
            [
                'id' => 2,
                'picture' => 'assets/images/flower.png',
                'username' => 'William Shakespeare',
                'email' => 'william.shakespeare@gmail.com',
                'nb_books' => 200
            ],
            [
                'id' => 3,
                'picture' => 'assets/images/quote.jpg',
                'username' => 'Taha Hussein',
                'email' => 'taha.hussein@gmail.com',
                'nb_books' => 300
            ]
        ];

        // Calcul des totaux
        $totalAuthors = count($authors);
        $totalBooks = array_sum(array_column($authors, 'nb_books'));

        return $this->render('author/list.html.twig', [
            'authors' => $authors,
            'totalAuthors' => $totalAuthors,
            'totalBooks' => $totalBooks
        ]);
    }

    // ---------- PAGE DÉTAILS D’UN AUTEUR ----------
    #[Route('/author/details/{id}', name: 'author_details')]
    public function authorDetails(int $id): Response
    {
        $authors = [
            [
                'id' => 1,
                'picture' => 'assets/images/enginer.jpg',
                'username' => 'Victor Hugo',
                'email' => 'victor.hugo@gmail.com',
                'nb_books' => 100
            ],
            [
                'id' => 2,
                'picture' => 'assets/images/flower.png',
                'username' => 'William Shakespeare',
                'email' => 'william.shakespeare@gmail.com',
                'nb_books' => 200
            ],
            [
                'id' => 3,
                'picture' => 'assets/images/quote.jpg',
                'username' => 'Taha Hussein',
                'email' => 'taha.hussein@gmail.com',
                'nb_books' => 300
            ]
        ];

        $author = null;
        foreach ($authors as $a) {
            if ($a['id'] === $id) {
                $author = $a;
                break;
            }
        }

        if (!$author) {
            throw $this->createNotFoundException("Auteur avec l'ID $id introuvable !");
        }

        return $this->render('author/showAuthor.html.twig', [
            'author' => $author
        ]);
    }
        #[Route('/ShowAllAuthor',name:'ShowAllAuthor')]
        public function ShowAllAuthor(AuthorRepository $repo): Response {
            $authors=$repo->findAll();
            return $this->render('author/listAllAuthor.html.twig',['list'=>$authors]);
        }
            // ---------- 5) Ajouter un auteur avec des données statiques ----------
    #[Route('/author/addStatic', name: 'author_add_static')]
    public function addStatic(EntityManagerInterface $em): Response
    {
        $author = new Author();
        $author->setUsername('Auteur Statique');
        $author->setEmail('static.author@example.com');
        $author->setAge(45);

        $em->persist($author);
        $em->flush();

        $this->addFlash('success', 'Auteur statique ajouté.');
        return $this->redirectToRoute('ShowAllAuthor');
    }
 // ---------- 7) Ajouter un auteur via formulaire (GET/POST) ----------
    #[Route('/author/add', name: 'author_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($author);
            $em->flush();

            $this->addFlash('success', 'Auteur ajouté.');
            return $this->redirectToRoute('ShowAllAuthor');
        }

        return $this->render('author/add.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un auteur'
        ]);
    }
// ---------- 9) Modifier un auteur (affiche le formulaire et sauvegarde) ----------
    #[Route('/author/edit/{id}', name: 'author_edit')]
    public function edit(Author $author, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Auteur modifié.');
            return $this->redirectToRoute('ShowAllAuthor');
        }

        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier l\'auteur'
        ]);
    }
 // ---------- 10) Supprimer un auteur (méthode POST avec CSRF) ----------
    #[Route('/author/delete/{id}', name: 'author_delete', methods: ['POST'])]
    public function delete(Request $request, Author $author, EntityManagerInterface $em): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $author->getId(), $submittedToken)) {
            $em->remove($author);
            $em->flush();
            $this->addFlash('success', 'Auteur supprimé.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('ShowAllAuthor');
    }
    }
