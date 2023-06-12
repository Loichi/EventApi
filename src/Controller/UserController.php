<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    private $userRepository;
    private $serializer;
    private $em;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->em = $em;
    }


    //FIND
    #[Route('/user', name: 'userList', methods: ['GET'])]
    public function getUserList(): JsonResponse
    {
        $userList = $this->userRepository->findAll();

        $jsonUserList = $this->serializer->serialize($userList, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }


    //FIND ONE
    #[Route('/user/{id}', name: 'user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if ($user) {

            $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUsers']);

            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }



    //Methode qui Supprime un utilisateur et les events dont il est le propriétaire et les invitations quil a recu 
    #[Route('/user/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, InvitationRepository $invitationRepository): JsonResponse
    {
        // Récupérer les événements organisés par l'utilisateur
        $eventsOrganized = $user->getEventsOrganized();
    
        // Supprimer les événements organisés par l'utilisateur
        foreach ($eventsOrganized as $event) {
            $this->em->remove($event);
        }
    
        // Supprimer les invitations reçues par l'utilisateur
        $receivedInvitations = $invitationRepository->findBy(['invitee' => $user]);
        foreach ($receivedInvitations as $invitation) {
            $this->em->remove($invitation);
        }
    
        // Supprimer l'utilisateur
        $this->em->remove($user);
        $this->em->flush();
    
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    


    //CREATE
    #[Route('/user', name: "createUser", methods: ['POST'])]
    public function createUser(Request $request,): JsonResponse
    {

        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setCreatedAt(new DateTimeImmutable());
        $this->em->persist($user);
        $this->em->flush();

        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }



    //UPDATE
    #[Route('/user/{id}', name: "updateUser", methods: ['PUT'])]
    public function updateUser(Request $request, int $id): JsonResponse
    {

        $user = $this->userRepository->find($id);

        if ($user) {

            $updatedUser = $this->serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
            $updatedUser->setUpdatedAt(new DateTimeImmutable());
            $this->em->persist($updatedUser);
            $this->em->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    //Recuperation des Events organisé en fonction de l'id d'un User

    #[Route('/user/{id}/events-organized', name: 'eventsOrganizedList', methods: ['GET'])]
    public function getEventsOrganizedList(int $id, UserRepository $userRepository): JsonResponse
    {
        // Récupéreration l'utilisateur à partir de son ID
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupéreration de la liste des événements organisés par l'utilisateur
        $organizedEvents = $user->getEventsOrganized();

        // Construire la réponse JSON avec les données des événements
        $responseData = [];
        foreach ($organizedEvents as $event) {
            $responseData[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'location' => $event->getLocation(),
                'date' => $event->getDate()->format('Y-m-d H:i:s'),
                'price' => $event->getPrice(),
                'createdAt' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $event->getUpdatedAt() ? $event->getUpdatedAt()->format('Y-m-d H:i:s') : null
            ];
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    // Récupération des invitationsReceived en fonction de l'id d'un User
    #[Route('/user/{id}/received-invitations', name: 'receivedInvitationList', methods: ['GET'])]
    public function getReceivedInvitationList(int $id, UserRepository $userRepository): JsonResponse
    {
        // Récupération de l'utilisateur à partir de son ID
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupération des événements reçus par l'utilisateur
        $receivedInvitations = $user->getInvitationReceived();

        // Construire la réponse JSON avec les données des événements
        $responseData = [];
        foreach ($receivedInvitations as $invitation) {

            $event = $invitation->getEvent(); // Récupérer l'objet Event associé à l'invitation
            $inviteur = $invitation->getInviteur(); // Récupérer l'objet User associé à l'invitation

            $responseData[] = [
                'id' => $invitation->getId(),
                'question' => $invitation->getQuestion(),
                'response' => $invitation->getResponse(),
                'createdAt' => $invitation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $invitation->getUpdatedAt() ? $invitation->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'inviteur' => [
                    'id' => $inviteur->getId(),
                    'username' => $inviteur->getUsername()
                ],
                'event' => [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                ]
            ];
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }


    // Récupération des invitationsSent en fonction de l'id d'un User
    #[Route('/user/{id}/sent-invitations', name: 'sentInvitationList', methods: ['GET'])]
    public function getSentInvitationList(int $id, UserRepository $userRepository): JsonResponse
    {
        // Récupération de l'utilisateur à partir de son ID
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupération des invitations envoyées par l'utilisateur
        $sentInvitations = $user->getInvitationsSent();

        // Construire la réponse JSON avec les données des invitations
        $responseData = [];
        foreach ($sentInvitations as $invitation) {
            $event = $invitation->getEvent(); // Récupérer l'objet Event associé à l'invitation
            $invitee = $invitation->getInvitee(); // Récupérer l'objet User associé à l'invitation

            $responseData[] = [
                'id' => $invitation->getId(),
                'question' => $invitation->getQuestion(),
                'response' => $invitation->getResponse(),
                'createdAt' => $invitation->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $invitation->getUpdatedAt() ? $invitation->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'event' => [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                ],
                'invitee' => [
                    'id' => $invitee->getId(),
                    'username' => $invitee->getUsername()
                ]
            ];
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}