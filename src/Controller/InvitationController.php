<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Invitation;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvitationController extends AbstractController
{
    private $invitationRepository;
    private $serializer;
    private $em;

    public function __construct(InvitationRepository $invitationRepository, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->invitationRepository = $invitationRepository;
        $this->serializer = $serializer;
        $this->em = $em;
    }


    //FIND
    #[Route('/invitation', name: 'invitationList', methods: ['GET'])]
    public function getInvitationList(): JsonResponse
    {
        $invitationList = $this->invitationRepository->findAll();

        $jsonEventList = $this->serializer->serialize($invitationList, 'json', ['groups' => 'getInvitations']);

        return new JsonResponse($jsonEventList, Response::HTTP_OK, [], true);
    }


    //FIND ONE
    #[Route('/invitation/{id}', name: 'invitation', methods: ['GET'])]
    public function getInvitationById(int $id): JsonResponse
    {
        $invitation = $this->invitationRepository->find($id);

        if ($invitation) {

            $jsonInvitations = $this->serializer->serialize($invitation, 'json', ['groups' => 'getInvitations']);

            return new JsonResponse($jsonInvitations, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    //DELETE
    #[Route('/invitation/{id}', name: 'deleteInvitation', methods: ['DELETE'])]
    public function deleteInvitation(Invitation $invitation): JsonResponse
    {
        $this->em->remove($invitation);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    //CREATE
    #[Route('/invitation', name: "createInvitation", methods: ['POST'])]
    public function createInvitation(Request $request): JsonResponse
    {

        $invitation = $this->serializer->deserialize($request->getContent(), Invitation::class, 'json');
        $invitation->setCreatedAt(new DateTimeImmutable());
        $this->em->persist($invitation);
        $this->em->flush();

        $jsonInvitation = $this->serializer->serialize($invitation, 'json', ['groups' => 'getEvents']);

        return new JsonResponse($jsonInvitation, Response::HTTP_CREATED, [], true);
    }



    //UPDATE
    #[Route('/invitation/{id}', name: "updateInvitation", methods: ['PUT'])]
    public function updateInvitation(Request $request, int $id): JsonResponse
    {

        $invitation = $this->invitationRepository->find($id);

        if ($invitation) {

            $updatedInvitation = $this->serializer->deserialize($request->getContent(), Invitation::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $invitation]);
            $updatedInvitation->setUpdatedAt(new DateTimeImmutable());
            $this->em->persist($updatedInvitation);
            $this->em->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    //Accepte l'invitation et place l'user dans les guest de l event
    #[Route('/invitation/{id}/accept', name: 'acceptInvitation', methods: ['PUT'])]
    public function acceptInvitation(Invitation $invitation): JsonResponse
    {
        // Vérifier si l'invitation est déjà acceptée
        if ($invitation->isAccepted()) {
            return new JsonResponse(['message' => 'Invitation already accepted'], Response::HTTP_BAD_REQUEST);
        }

        // Mettre à jour le statut de l'invitation en "accepted"
        $invitation->setAccepted(true);

        // Récupérer l'utilisateur invité
        $guest = $invitation->getInvitee();

        // Récupérer l'événement associé à l'invitation
        $event = $invitation->getEvent();

        // Ajouter l'utilisateur invité en tant que guest de l'événement
        $event->addGuest($guest);

        // Enregistrer les modifications dans la base de données
        $this->em->flush();

        return new JsonResponse(['message' => 'Invitation accepted and user added as guest'], Response::HTTP_OK);
    }

    //Refus de l'invitation et suppression de celle ci
    #[Route('/invitation/{id}/reject', name: 'rejectInvitation', methods: ['DELETE'])]
    public function rejectInvitation(Invitation $invitation): JsonResponse
    {
        // Supprimer l'invitation
        $this->em->remove($invitation);

        // Enregistrer les modifications dans la base de données
        $this->em->flush();

        return new JsonResponse(['message' => 'Invitation rejected and deleted'], Response::HTTP_OK);
    }
}
