<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $openAIApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $openAIApiKey;
    }

    public function generateDescription(string $itemType, string $itemName, array $characteristics = []): string
    {
        $prompt = "Génère une description technique concise pour $itemName (équipement de camping).\n";
        $prompt .= "Caractéristiques clés : " . ($characteristics ? implode(', ', $characteristics) : "non spécifiées") . "\n";
        $prompt .= "Format : 2-3 phrases techniques, ton professionnel";

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 150
                ]
            ]);

            $data = $response->toArray();
            
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Réponse OpenAI invalide');
            }
            
            return $data['choices'][0]['message']['content'];
            
        }  catch (\Exception $e) {
            error_log("Erreur OpenAI: " . $e->getMessage());
            return "Description non disponible - erreur technique";
        }
    }
}