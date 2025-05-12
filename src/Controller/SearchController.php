<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MaterielRepository;
use App\Repository\LoisirRepository;


class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(
        Request $request,
        MaterielRepository $materielRepository,
        LoisirRepository $loisirRepository
    ) {
        $searchTerm = $request->query->get('searchbar', '');
        
        return $this->render('loisir/index.html.twig', [
            'materiels' => $materielRepository->searchByName($searchTerm),
            'loisirs' => $loisirRepository->searchByName($searchTerm),
            'searchTerm' => $searchTerm
        ]);
    }
}