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
     *     name="author",
     *     methods="GET"
     * )
     */
    public function show(int $id)
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AuthorController.php',
        ]);
    }

    /**
     * Store an Author
     *
     * @Route(
     *     "/author",
     *     name="store",
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

        return new JsonResponse($author);
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
