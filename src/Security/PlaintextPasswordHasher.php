<?php
namespace App\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PlaintextPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return $plainPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $hashedPassword === $plainPassword;
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
