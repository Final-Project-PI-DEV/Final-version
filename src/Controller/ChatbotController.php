<?php

// src/Controller/ChatbotController.php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;

class ChatbotController extends AbstractController
{
    private $httpClient;
    private $em;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em)
    {
        $this->httpClient = $httpClient;
        $this->em = $em;
    }

    private function checkForBadWords(string $content): bool
    {
        try {
            $githubToken = $this->getParameter('github_token');
            if (empty($githubToken)) {
                throw new \Exception('GitHub token is not configured');
            }

            $url = 'https://models.inference.ai.azure.com/chat/completions';
            $data = [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a content moderator. Analyze the following text and respond with only "true" if it contains inappropriate language, profanity, or offensive content, or "false" if it is clean.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $content
                    ]
                ],
                'model' => 'Codestral-2501'
            ];
            $jsonData = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $githubToken,
                'x-ms-model-mesh-model-name: Codestral-2501'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new \Exception('cURL error: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($statusCode !== 200) {
                throw new \Exception('API request failed with status: ' . $statusCode . ' and response: ' . $response);
            }

            $decodedResponse = json_decode($response, true);
            if (!isset($decodedResponse['choices'][0]['message']['content'])) {
                throw new \Exception('Unexpected API response format');
            }

            $result = strtolower(trim($decodedResponse['choices'][0]['message']['content']));
            return $result === 'true';
        } catch (\Exception $e) {
            error_log('Bad word detection failed: ' . $e->getMessage());
            return false;
        }
    }

    #[Route("/chatbot", name: "chatbot", methods: "POST")]
    public function chatbot(Request $request): JsonResponse
    {
        try {
            $userMessage = $request->request->get('message');

            if ($this->checkForBadWords($userMessage)) {
                $this->addFlash('error', 'Inappropriate content detected.');
                return $this->json(['response' => 'I cannot respond to inappropriate requests.']);
            }

            // Fetch relevant data from the database
            $posts = $this->em->getRepository(Post::class)->findAll();
            if (empty($posts)) {
                $this->addFlash('info', 'No posts found in the database.');
                return $this->json(['response' => 'I don\'t have any posts to share at the moment.']);
            }

            $knowledgeBase = [];
            foreach ($posts as $post) {
                $knowledgeBase[] = [
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'lieu' => $post->getLieu(),
                ];
            }

            // Construct the prompt for OpenAI
            $prompt = "You are a chatbot that answers questions based on the following information:\n\n";
            foreach ($knowledgeBase as $item) {
                $prompt .= "Title: " . $item['title'] . "\n";
                $prompt .= "Content: " . $item['content'] . "\n";
                $prompt .= "Lieu: " . $item['lieu'] . "\n\n";
            }
            $prompt .= "User question: " . $userMessage . "\n";
            $prompt .= "Answer: ";

            $githubToken = $this->getParameter('github_token');
            $url = 'https://models.inference.ai.azure.com/chat/completions';

            $data = [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'model' => 'Codestral-2501'
            ];
            $jsonData = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $githubToken,
                'x-ms-model-mesh-model-name: Codestral-2501'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new \Exception('cURL error: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($statusCode !== 200) {
                throw new \Exception('API request failed with status: ' . $statusCode . ' and response: ' . $response);
            }

            $decodedResponse = json_decode($response, true);
            if (!isset($decodedResponse['choices'][0]['message']['content'])) {
                throw new \Exception('Unexpected API response format');
            }

            $result = $decodedResponse['choices'][0]['message']['content'];
            $this->addFlash('success', 'Response generated successfully.');
            return $this->json(['response' => $result]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while processing your request.');
            return $this->json(['response' => 'Sorry, I encountered an error while processing your request.']);
        }
    }
}
