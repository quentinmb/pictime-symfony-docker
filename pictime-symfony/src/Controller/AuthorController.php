<?php

namespace App\Controller;

use App\Entity\Author;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    static $model = Author::class;

    /**
     * @Route(
     *     "/author/{id}",
     *     name="author.delete",
     *     methods="DELETE"
     * )
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
     * @return Author
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
     * @return The list of Authors
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
