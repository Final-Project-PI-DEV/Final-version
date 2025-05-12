<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: "post_likes")]  // Rename the table to avoid the reserved keyword "like"
class Like
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)] // User cannot be null
    private ?User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: false)] // Post cannot be null
    private ?Post $post;

    public function __construct(User $user, Post $post)
    {
        $this->user = $user;
        $this->post = $post;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }
}
