<?php

namespace App\Controller\Api;

use App\Entity\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploadController extends AbstractController
{
    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        // Récupérer l'image et le titre depuis la requête
        $uploadedFile = $request->files->get('image');
        $titre = $request->request->get('titre');

        // Vérification si l'image et le titre sont présents
        if (!$uploadedFile || !$titre) {
            return $this->json(['status' => 'error', 'message' => 'Image ou titre manquant.'], 400);
        }

        // Générer un nom de fichier sécurisé
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // Déplacer le fichier uploadé vers le dossier configuré dans Symfony
        $uploadedFile->move(
            $this->getParameter('images_directory'),
            $newFilename
        );

        // Sauvegarder les informations de l'événement en base de données
        $evenement = new Events();
        $evenement->setTitre($titre);
        $evenement->setImage($newFilename);
        $em->persist($evenement);
        $em->flush();

        // Retourner la réponse JSON avec le statut et le nom du fichier
        return $this->json(['status' => 'success', 'filename' => $newFilename]);
    }
}
