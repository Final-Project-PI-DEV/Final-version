<?php

namespace App\Service;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;

class PredictionService
{
    private $restaurantRepository;

    public function __construct(RestaurantRepository $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
    }

    public function predictBestRestaurants(): array
    {
        $restaurants = $this->restaurantRepository->findAll();

        // Simuler un modèle de prédiction basé sur le rating moyen
        $predictions = [];
        foreach ($restaurants as $restaurant) {
            $rating = $restaurant->getAverageRating();
            // Simuler une prédiction basée sur le rating
            $predictedScore = $this->simulatePrediction($rating);
            $predictions[] = [
                'restaurant' => $restaurant,
                'predictedScore' => $predictedScore,
            ];
        }

        // Trier les restaurants par score prédit
        usort($predictions, function ($a, $b) {
            return $b['predictedScore'] <=> $a['predictedScore'];
        });

        return $predictions;
    }

    private function simulatePrediction(float $rating): float
    {
        // Simuler une prédiction basée sur un modèle simple
        return $rating * 1.2;
    }
}
