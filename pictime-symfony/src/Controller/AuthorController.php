<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    static $model = Author::class;

    /**
     * @Route(
     *     "/author/{id}",
     *     name="author.update",
     *     methods="PATCH"
     * )
     * @param int $id
     * @param ValidatorInterface $validator
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update(int $id, ValidatorInterface $validator, Request $request)
    {
        $author = $this->getDoctrine()
            ->getRepository(self::$model)
            ->find($id);

        if(empty($author)){
            return new Response(sprintf('The Author with id = %s does not exist !', $id), 404);
        }

        $author->setFirstName($request->request->get('firstname'));
        $author->setLastName($request->request->get('lastname'));

        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            return $this->json([
                'success' => false,
                'message' => (string) $errors
            ], 403);
        }

        //Update the author
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->json([
            'id' => $author->getId(),
            'lastname' => $author->getLastname(),
            'firstname' => $author->getFirstname(),
        ], 200);
    }

    /**
     * @Route(
     *     "/author/{id}",
     *     name="author.delete",
     *     methods="DELETE"
     * )
     * @param int $id
     * @return Response
     */
    public function delete(int $id) : Response
    {
        $author = $this->getDoctrine()
            ->getRepository(self::$model)
            ->find($id);

        if(empty($author)){
            return new Response(sprintf('The Author with id = %s does not exist !', $id), 404);
        }

        $entityManager = $this->getDoctrine()->getManager();

        //Remove books
        foreach ($author->getBooks() as $book) {
            $entityManager->remove($book);
        }

        //Remove Author
        $entityManager->remove($author);

        $entityManager->flush();

        return new Response('', 200);
    }

    /**
     * @Route(
     *     "/author/{id}",
     *     name="author",
     *     methods="GET"
     * )
     * @param int $id
     * @return JsonResponse|Response
     */
    public function show(int $id)
    {
        $author = $this->getDoctrine()
            ->getRepository(self::$model)
            ->find($id);

        if(empty($author)){
            return new Response(sprintf('The Author with id = %s does not exist !', $id), 404);
        }

        $books = array();
        foreach ($author->getBooks() as $book) {
            $books[] = [
              'id' => $book->getId(),
              'title' => $book->getTitle()
            ];
        }

        return $this->json([
                'id' => $author->getId(),
                'lastname' => $author->getLastname(),
                'firstname' => $author->getFirstname(),
                'books' => $books
        ]);
    }

    /**
     * Store an Author
     *
     * @Route(
     *     "/author",
     *     name="author.store",
     *     methods={"POST"}
     *      )
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function store(Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $author = new self::$model;
        $author->setFirstName($request->request->get('firstname'));
        $author->setLastName($request->request->get('lastname'));

        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => (string) $errors
            ]);
        }

        //Save the author
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $author->getId(),
            'lastname' => $author->getLastname(),
            'firstname' => $author->getFirstname()
        ]);
    }


    /**
     * @Route(
     *     "/author",
     *     name="author.list",
     *     methods="GET"
     * )
     * @param SerializerInterface $serializer
     * @return JsonResponse list of Authors
     */
    public function getAuthors(SerializerInterface $serializer) : JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $authorsJSON = $em->createQuery('SELECT a FROM App:Author a')->getArrayResult();
        return new JsonResponse(
            $authorsJSON
        );
    }
}
