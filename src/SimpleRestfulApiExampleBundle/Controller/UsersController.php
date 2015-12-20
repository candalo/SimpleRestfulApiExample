<?php

namespace SimpleRestfulApiExampleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use SimpleRestfulApiExampleBundle\Entity\User;


/**
 * The controller that will manage the requests for the users
 * @author Lucas Soares Candalo
 */
class UsersController extends Controller
{
    // Constants
    const REPOSITORY = 'SimpleRestfulApiExampleBundle:User';

    /**
     * List all users
     * @Route("/users")
     * @Method("GET")
     */
    public function listAction()
    {
        // Get all users of the db
        $users = $this->getDoctrine()
            ->getRepository(UsersController::REPOSITORY)
            ->createQueryBuilder('e')
            ->select('e')
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);


        return new JsonResponse($users);
    }

    /**
     * List a user by id
     * @param id - The user id
     * @Route("/users/{id}")
     * @Method("GET")
     */
    public function getAction($id)
    {

        $user = $this->getDoctrine()->getRepository(UsersController::REPOSITORY)->findOneById($id);

        //Verifies if user exist
        if(!$user)
            return new JsonResponse(array('error'=>'user not found'), JsonResponse::HTTP_NOT_FOUND);

        $user = $this->getDoctrine()
            ->getRepository(UsersController::REPOSITORY)
            ->createQueryBuilder('e')
            ->select('e')
            ->where('e.id = ?1')
            ->setParameters(array(1 => $id))
            ->getQuery()
            ->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return new JsonResponse($user);
    }

    /**
     * Create a new user
     * @param request  The json request
     * @return JsonResponse
     * @Route("/users")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        // Get the json body
        if($content = $request->getContent()) {
            // Decode the json object to an array
            $parametersAsArray = json_decode($content, true);
        }

        // Verifies if the value of the name was not passed in the request
        if(!isset($parametersAsArray['name']))
            return new JsonResponse(array('error'=>'name value does not exist'), JsonResponse::HTTP_BAD_REQUEST);

        // Verifies if the value of the age was not passed in the request
        if(!isset($parametersAsArray['age']))
            return new JsonResponse(array('error'=>'age value does not exist'), JsonResponse::HTTP_BAD_REQUEST);

        // Create the user object
        $user = new User();
        $user->setName($parametersAsArray['name']);
        $user->setAge($parametersAsArray['age']);

        // Doctrine manager
        $em = $this->getDoctrine()->getManager();

        // Save the object in db
        $em->persist($user);
        $em->flush();

        return new JsonResponse(array('success'=>'user created'), JsonResponse::HTTP_CREATED);
    }

    /**
     * Edit a user by id
     * @param id The user id
     * @param request The json request
     * @return JsonResponse
     * @Route("/users/{id}")
     * @Method("PUT")
     */
    public function editAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository(UsersController::REPOSITORY)->findOneById($id);

        //Verifies if user exist
        if(!$user)
            return new JsonResponse(array('error'=>'user not found'), JsonResponse::HTTP_NOT_FOUND);

        // Get the json body
        if($content = $request->getContent()) {
            // Decode the json object to an array
            $requestBodyAsArray = json_decode($content, true);
        }

        foreach($requestBodyAsArray as $key => $value) {
            // Convert the first letter of the key to uppercase
            $key = ucFirst($key);

            // Method that will be invoked
            $method = "set".$key;

            $user->$method($value);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // Load the user to return in the json
        $user = $this->getDoctrine()
            ->getRepository(UsersController::REPOSITORY)
            ->createQueryBuilder('e')
            ->select('e')
            ->where('e.id = ?1')
            ->setParameters(array(1 => $id))
            ->getQuery()
            ->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return new JsonResponse($user);
    }

    /**
     * Delete a user by id
     * @param id The user id
     * @return JsonResponse
     * @Route("/users/{id}")
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        // Doctrine manager
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(UsersController::REPOSITORY);

        // Loads the user
        $user = $repository->find($id);

        // Verifies if the user exist
        if(!$user)
            return new JsonResponse(array('error'=>'user not found'), JsonResponse::HTTP_NOT_FOUND);

        $em->remove($user);
        $em->flush();

        return new JsonResponse(array('success'=>'user deleted'));
    }
}
