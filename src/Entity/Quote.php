<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuoteRepository")
 */
class Quote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *@Assert\NotBlank()
     *@Assert\Length(max=255, maxMessage="This is a quote not a book")
     *@Assert\Regex(match=false,pattern="/<[^>]*>/", message="No <> tags allowed")
     *
     * @ORM\Column(type="text")
     *
     */
    private $content;

    /**
     * @Assert\Valid()
     * @ORM\ManyToOne(targetEntity="App\Entity\QuoteAuthor", inversedBy="quotes", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @Serializer\Exclude()
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getAuthor(): ?QuoteAuthor
    {
        return $this->author;
    }

    public function setAuthor(?QuoteAuthor $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
