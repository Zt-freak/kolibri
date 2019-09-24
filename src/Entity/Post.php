<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * One Post has Many Posts.
     * @ORM\OneToMany(targetEntity="Post", mappedBy="parent")
     */
    private $children;

    /**
     * Many Posts have One Post.
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString() {
        return (string) $this->getId();
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getChildren(): ?Post
    {
        return $this->children;
    }

    public function getParent(): ?Post
    {
        return $this->parent;
    }

    public function setparent(?Post $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
