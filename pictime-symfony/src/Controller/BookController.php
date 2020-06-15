<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookController extends AbstractController
{
    static $model = Book::class;
    static $author = Author::class;


    /**
     * @Route(
     *     "/book/{id}",
     *     name="book.delete",
     *     methods="DELETE"
     * )
     */
    public function delete(int $id) : Response
    {
        $book = $this->getDoctrine()
            ->getRepository(self::$model)
            ->find($id);

        if(empty($book)){
            return new Response(sprintf('The Book with id = %s does not exist !', $id), 404);
        }

        $entityManager = $this->getDoctrine()->getManager();

        //Remove Author
        $entityManager->remove($book);
        $entityManager->flush();

        return new Response('', 200);
    }

    /**
     * Store an Book
     *
     * @Route(
     *     "/book",
     *     name="book.store",
     *     methods={"POST"}
     *      )
     *
     * @return Book
     */
    public function store(Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $book = new self::$model;
        $book->setTitle($request->request->get('title'));

        $author = $this->getDoctrine()->getRepository(self::$author)
            ->findOneBy(['lastname' => $request->request->get('author-name')]);

        $book->setAuthor($author);

        $errors = $validator->validate($book);

        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => (string) $errors
            ]);
        }

        //Save the book
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse([
            'title' => $book->getTitle(),
            'author' => $author->getLastname() . ' ' . $author->getFirstName()
        ]);
    }



    /**
     * @Route(
     *     "/book/{id}",
     *     name="book",
     *     methods="GET"
     * )
     */
    public function show(int $id)
    {
        $book = $this->getDoctrine()
            ->getRepository(self::$model)
            ->find($id);

        if(empty($book)){
            return new Response(sprintf('The Book with id = %s does not exist !', $id), 404);
        }

        $author = $book->getAuthor();
        return $this->json([
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => [
                'id' => $author->getId(),
                'firstname' => $author->getFirstname(),
                'lastname' => $author->getLastName(),
            ]
        ]);
    }




    /**
     * @Route(
     *     "/book",
     *     name="book.list",
     *     methods="GET"
     * )
     * @return The list of Books
     */
    public function getBooks(SerializerInterface $serializer) : JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $booksJSON = $em->createQuery('SELECT b FROM App:Book b')->getArrayResult();
        return new JsonResponse(
            $booksJSON
        );
    }
}
