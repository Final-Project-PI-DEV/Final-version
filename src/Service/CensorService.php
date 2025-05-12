<?php

namespace App\Service;

class CensorService
{
    private array $badWords = [
        'merde', 'putain', 'connard', 'pute', 'salope', 'enculé',
        // Ajoutez d'autres mots à censurer ici
    ];

    public function censorText(string $text): string
    {
        $pattern = '/\b(' . implode('|', array_map('preg_quote', $this->badWords)) . ')\b/i';
        return preg_replace_callback($pattern, function($matches) {
            return str_repeat('*', strlen($matches[0]));
        }, $text);
    }
}
