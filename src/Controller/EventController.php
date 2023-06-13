<?php

namespace App\Controller;

use App\Entity\Event;

use DateTimeImmutable;
use App\Entity\Invitation;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;







class EventController extends AbstractController
{

    private $eventRepository;
    private $serializer;
    private $em;

    public function __construct(EventRepository $eventRepository, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->eventRepository = $eventRepository;
        $this->serializer = $serializer;
        $this->em = $em;
    }


    //FIND
    #[Route('/event', name: 'eventList', methods: ['GET'])]
    public function getEventList(): JsonResponse
    {
        $eventList = $this->eventRepository->findAll();

        $jsonEventList = $this->serializer->serialize($eventList, 'json', ['groups' => 'getEvents']);

        return new JsonResponse($jsonEventList, Response::HTTP_OK, [], true);
    }


    //FIND ONE
    #[Route('/event/{id}', name: 'event', methods: ['GET'])]
    public function getEventById(int $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if ($event) {

            $jsonEvent = $this->serializer->serialize($event, 'json', ['groups' => 'getEvents']);

            return new JsonResponse($jsonEvent, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    //DELETE
    #[Route('/event/{id}', name: 'deleteEvent', methods: ['DELETE'])]
    public function deleteEvent(Event $event): JsonResponse
    {
        $this->em->remove($event);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    //Methode de création d'un Event qui va lié un organizer en fonction de son id et qui crée et envoie les invitations
    #[Route('/event/{organizerId}', name: "createEvent", methods: ['POST'])]
    public function createEvent(Request $request, UserRepository $userRepository, int $organizerId): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Récupération des données de l'événement depuis la requête
        $eventData = [
            'title' => $requestData['title'],
            'description' => $requestData['description'],
            'location' => $requestData['location'],
            'date' => new \DateTime($requestData['date']),
            'price' => $requestData['price']
        ];

        // Récupération de l'utilisateur organisateur de l'événement en fonction de son ID passé dans la route
        $organizer = $userRepository->find($organizerId);
        if (!$organizer) {
            return new JsonResponse(['message' => 'Organizer not found'], Response::HTTP_NOT_FOUND);
        }

        // Création d'un objet Event
        $event = new Event();
        $event->setTitle($eventData['title'])
            ->setDescription($eventData['description'])
            ->setLocation($eventData['location'])
            ->setDate($eventData['date'])
            ->setPrice($eventData['price'])
            ->setOrganizer($organizer)
            ->setCreatedAt(new \DateTimeImmutable());

        // Récupération des utilisateurs invités depuis la requête
        $invitedUserIds = $requestData['invitedUsers'] ?? [];
        $invitedUsers = $userRepository->findBy(['id' => $invitedUserIds]);

        // Création des invitations pour les utilisateurs invités
        foreach ($invitedUsers as $invitedUser) {
            $invitation = new Invitation();
            $invitation->setEvent($event)
                ->setInviteur($organizer)
                ->setInvitee($invitedUser)
                ->setQuestion($requestData['question'])
                ->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($invitation);
        }

        // Persist et flush les invitations
        $this->em->flush();

        // Retourner la réponse JSON avec l'événement créé
        $responseData = [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'date' => $event->getDate()->format('Y-m-d H:i:s'),
            'price' => $event->getPrice(),
            'createdAt' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
            'organizer' => [
                'id' => $organizer->getId(),
                'username' => $organizer->getUsername()
            ]
        ];

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }




    //UPDATE
    #[Route('/event/{id}', name: "updateEvent", methods: ['PUT'])]
    public function updateEvent(Request $request, int $id): JsonResponse
    {

        $event = $this->eventRepository->find($id);

        if ($event) {

            $updatedEvent = $this->serializer->deserialize($request->getContent(), Event::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $event]);
            $updatedEvent->setUpdatedAt(new DateTimeImmutable());
            $this->em->persist($updatedEvent);
            $this->em->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    //Récupérer l'organisateur en fonction de l'id de l'Event
    #[Route('/event/{id}/organizer', name: 'eventOrganizer', methods: ['GET'])]
    public function getEventOrganizer(int $id, EventRepository $eventRepository): JsonResponse
    {
        // Récupération de l'événement à partir de son ID
        $event = $eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupération de l'organisateur de l'événement
        $organizer = $event->getOrganizer();

        if (!$organizer) {
            return new JsonResponse(['message' => 'Organizer not found'], Response::HTTP_NOT_FOUND);
        }

        // Construire la réponse JSON avec les données de l'organisateur
        $responseData = [
            'id' => $organizer->getId(),
            'username' => $organizer->getUsername(),
            // Ajoutez les autres attributs de l'organisateur que vous souhaitez inclure dans la réponse
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    //Récupérer la liste des invitations lié a un event en fonction de son id
    #[Route('/event/{id}/invitations', name: 'eventInvitations', methods: ['GET'])]
    public function getEventInvitations(int $id, EventRepository $eventRepository): JsonResponse
    {
        // Récupération de l'événement à partir de son ID
        $event = $eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        // Récupération des invitations liées à l'événement
        $invitations = $event->getInvitations();

        // Construire la réponse JSON avec les données des invitations
        $responseData = [];
        foreach ($invitations as $invitation) {

            $responseData[] = [
                'id' => $invitation->getId(),
                'question' => $invitation->getQuestion(),
                'response' => $invitation->getResponse(),

            ];
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
