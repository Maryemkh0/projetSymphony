<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Form\BookType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book')]
class BookController extends AbstractController
{
    // Ajouter un livre
    #[Route('/add', name: 'book_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setPublished(true);

            $author = $book->getAuthor();
            if ($author) {
                $author->setNbBooks(($author->getNbBooks() ?? 0) + 1);
            }

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', '✅ Livre ajouté avec succès !');
            return $this->redirectToRoute('book_list');
        }

        return $this->render('book/add.html.twig', ['form' => $form->createView()]);
    }

    // Liste des livres publiés et non publiés
    #[Route('/list', name: 'book_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(Book::class);
        $booksPublished = $repo->findBy(['published' => true]);
        $booksNotPublished = $repo->findBy(['published' => false]);

        return $this->render('book/list.html.twig', [
            'booksPublished' => $booksPublished,
            'booksNotPublished' => $booksNotPublished,
        ]);
    }

    // Modifier un livre
    #[Route('/edit/{id}', name: 'book_edit')]
    public function edit(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✏️ Livre modifié avec succès !');
            return $this->redirectToRoute('book_list');
        }

        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book
        ]);
    }

    // Supprimer un livre
    #[Route('/delete/{id}', name: 'book_delete', methods: ['POST'])]
    public function delete(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->request->get('_token'))) {
            $author = $book->getAuthor();

            $em->remove($book);
            $em->flush();

            if ($author) {
                $author->setNbBooks(max(0, ($author->getNbBooks() ?? 1) - 1));
                $em->flush();
            }

            $this->addFlash('success', '🗑️ Livre supprimé avec succès !');
        }

        return $this->redirectToRoute('book_list');
    }

    // Supprimer les auteurs sans livres
    #[Route('/delete-empty-authors', name: 'delete_empty_authors')]
    public function deleteEmptyAuthors(EntityManagerInterface $em): Response
    {
        $authors = $em->getRepository(Author::class)->findAll();
        $count = 0;

        foreach ($authors as $author) {
            if (($author->getNbBooks() ?? 0) === 0) {
                $em->remove($author);
                $count++;
            }
        }

        if ($count > 0) $em->flush();

        $this->addFlash('info', "🧹 $count auteur(s) supprimé(s) car aucun livre.");
        return $this->redirectToRoute('book_list');
    }

    // Détails d’un livre
    #[Route('/show/{id}', name: 'book_show')]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', ['book' => $book]);
    }
}
